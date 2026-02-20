<?php

namespace app\logic;

use flight\database\PdoWrapper;

/**
 * Logique de dispatch automatique des dons vers les besoins.
 * 
 * 3 modes de dispatch :
 *   - prioritaire   : par ordre de saisie/date ASC (les premiers saisis sont servis en premier)
 *   - minoritaire    : les besoins les plus petits (quantité ASC) sont servis en premier
 *   - proportionnelle: chaque besoin reçoit besoin/don
 */
class DispatchLogic
{
    public const MODE_PRIORITAIRE    = 'prioritaire';
    public const MODE_MINORITAIRE    = 'minoritaire';
    public const MODE_PROPORTIONNELLE = 'proportionnelle';

    /**
     * Exécuter le dispatch automatique.
     * Supprime les anciens dispatches et recalcule tout selon le mode choisi.
     *
     * @param PdoWrapper $db
     * @param string     $mode  'prioritaire' | 'minoritaire' | 'proportionnelle'
     */
    public static function executer(PdoWrapper $db, string $mode = self::MODE_PRIORITAIRE): void
    {
        // 1. Supprimer tous les dispatches existants
        $db->runQuery("DELETE FROM dispatch");

        // 2. Récupérer tous les détails de dons, triés par date du don puis par id
        $don_details = $db->fetchAll("
            SELECT dd.id AS dd_id, dd.type_besoin_id, dd.quantite AS dd_quantite, d.date_don
            FROM don_detail dd
            JOIN don d ON dd.don_id = d.id
            ORDER BY d.date_don ASC, dd.id ASC
        ");

        if ($mode === self::MODE_PROPORTIONNELLE) {
            self::executerProportionnelle($db, $don_details);
        } elseif ($mode === self::MODE_MINORITAIRE) {
            self::executerMinoritaire($db, $don_details);
        } else {
            self::executerPrioritaire($db, $don_details);
        }
    }

    /**
     * Mode PRIORITAIRE : les besoins saisis en premier (date ASC) sont servis en priorité.
     */
    private static function executerPrioritaire(PdoWrapper $db, array $don_details): void
    {
        foreach ($don_details as $dd) {
            $reste_don = (float) $dd['dd_quantite'];

            $besoins = $db->fetchAll("
                SELECT b.id AS besoin_id, b.quantite AS besoin_qte,
                       COALESCE(dispatched.total, 0) AS deja_dispatche
                FROM besoin b
                LEFT JOIN (
                    SELECT besoin_id, SUM(quantite) AS total
                    FROM dispatch
                    GROUP BY besoin_id
                ) dispatched ON dispatched.besoin_id = b.id
                WHERE b.type_besoin_id = ?
                  AND (b.quantite - COALESCE(dispatched.total, 0)) > 0
                ORDER BY b.date_saisie ASC, b.id ASC
            ", [(int) $dd['type_besoin_id']]);

            foreach ($besoins as $besoin) {
                $besoin_restant = (float) $besoin['besoin_qte'] - (float) $besoin['deja_dispatche'];
                if ($reste_don >= $besoin_restant) {
                    // Don suffisant : attribuer la totalité
                    $db->runQuery(
                        "INSERT INTO dispatch (don_detail_id, besoin_id, quantite) VALUES (?, ?, ?)",
                        [(int) $dd['dd_id'], (int) $besoin['besoin_id'], $besoin_restant]
                    );
                    $reste_don -= $besoin_restant;
                } else {
                    // Don insuffisant : attribuer 0
                    $db->runQuery(
                        "INSERT INTO dispatch (don_detail_id, besoin_id, quantite) VALUES (?, ?, 0)",
                        [(int) $dd['dd_id'], (int) $besoin['besoin_id']]
                    );
                }
            }
        }
    }

    /**
     * Mode MINORITAIRE : les besoins les plus petits (quantité ASC) sont servis en priorité.
     */
    private static function executerMinoritaire(PdoWrapper $db, array $don_details): void
    {
        foreach ($don_details as $dd) {
            $reste_don = (float) $dd['dd_quantite'];

            $besoins = $db->fetchAll("
                SELECT b.id AS besoin_id, b.quantite AS besoin_qte,
                       COALESCE(dispatched.total, 0) AS deja_dispatche
                FROM besoin b
                LEFT JOIN (
                    SELECT besoin_id, SUM(quantite) AS total
                    FROM dispatch
                    GROUP BY besoin_id
                ) dispatched ON dispatched.besoin_id = b.id
                WHERE b.type_besoin_id = ?
                  AND (b.quantite - COALESCE(dispatched.total, 0)) > 0
                ORDER BY b.quantite ASC, b.id ASC
            ", [(int) $dd['type_besoin_id']]);

            foreach ($besoins as $besoin) {
                $besoin_restant = (float) $besoin['besoin_qte'] - (float) $besoin['deja_dispatche'];
                if ($reste_don >= $besoin_restant) {
                    // Don suffisant : attribuer la totalité
                    $db->runQuery(
                        "INSERT INTO dispatch (don_detail_id, besoin_id, quantite) VALUES (?, ?, ?)",
                        [(int) $dd['dd_id'], (int) $besoin['besoin_id'], $besoin_restant]
                    );
                    $reste_don -= $besoin_restant;
                } else {
                    // Don insuffisant : attribuer 0
                    $db->runQuery(
                        "INSERT INTO dispatch (don_detail_id, besoin_id, quantite) VALUES (?, ?, 0)",
                        [(int) $dd['dd_id'], (int) $besoin['besoin_id']]
                    );
                }
            }
        }
    }

    /**
     * Mode PROPORTIONNEL : méthode du plus grand reste.
     *
     * Pour chaque type de besoin :
     *   1. Calculer la part de chaque besoin : (besoin_qte × total_don) / total_besoins
     *   2. Prendre le plancher (floor) de chaque part
     *   3. Calculer le reste = total_don - somme(planchers)
     *   4. Distribuer le reste (+1) aux besoins avec les plus grandes fractions
     *
     * Exemple : besoins = 1, 3, 5 (total=9), dons = 5
     *   Parts : 5/9=0.55, 15/9=1.66, 25/9=2.77
     *   Planchers : 0, 1, 2 (total=3)
     *   Reste = 5-3 = 2
     *   Fractions triées : 0.77 (besoin 5), 0.66 (besoin 3), 0.55 (besoin 1)
     *   +1 à besoin 5 → 3, +1 à besoin 3 → 2
     *   Résultat : 0, 2, 3
     */
    private static function executerProportionnelle(PdoWrapper $db, array $don_details): void
    {
        // 1. Regrouper les don_details par type et calculer le total par type
        $stock_par_type = [];
        $details_par_type = [];
        foreach ($don_details as $dd) {
            $type_id = (int) $dd['type_besoin_id'];
            if (!isset($stock_par_type[$type_id])) {
                $stock_par_type[$type_id] = 0;
                $details_par_type[$type_id] = [];
            }
            $stock_par_type[$type_id] += (float) $dd['dd_quantite'];
            $details_par_type[$type_id][] = [
                'dd_id'    => (int) $dd['dd_id'],
                'restant'  => (float) $dd['dd_quantite'],
            ];
        }

        // 2. Pour chaque type, calculer les allocations proportionnelles
        foreach ($stock_par_type as $type_id => $total_don) {
            if ($total_don <= 0) continue;

            $besoins = $db->fetchAll("
                SELECT b.id AS besoin_id, b.quantite AS besoin_qte
                FROM besoin b
                WHERE b.type_besoin_id = ?
                  AND b.quantite > 0
                ORDER BY b.date_saisie ASC, b.id ASC
            ", [$type_id]);

            if (empty($besoins)) continue;

            // Calculer le total des besoins pour ce type
            $total_besoins = 0;
            foreach ($besoins as $b) {
                $total_besoins += (float) $b['besoin_qte'];
            }

            if ($total_besoins <= 0) continue;

            // Étape 1 : part proportionnelle = (besoin_qte × total_don) / total_besoins
            $allocations = [];
            foreach ($besoins as $b) {
                $besoin_qte = (float) $b['besoin_qte'];
                $part_exacte = ($besoin_qte * $total_don) / $total_besoins;
                $plancher = (int) floor($part_exacte);
                $fraction = $part_exacte - $plancher;
                // Ne pas dépasser le besoin demandé
                $plancher = min($plancher, (int) $besoin_qte);

                $allocations[] = [
                    'besoin_id' => (int) $b['besoin_id'],
                    'besoin_qte' => $besoin_qte,
                    'plancher'  => $plancher,
                    'fraction'  => $fraction,
                ];
            }

            // Étape 2 : calculer le reste à distribuer
            $somme_planchers = 0;
            foreach ($allocations as $a) {
                $somme_planchers += $a['plancher'];
            }
            $reste = (int) round($total_don) - $somme_planchers;

            // Étape 3 : trier par fraction décroissante pour distribuer le reste
            $indices = array_keys($allocations);
            usort($indices, function ($a, $b) use ($allocations) {
                return $allocations[$b]['fraction'] <=> $allocations[$a]['fraction'];
            });

            // Distribuer +1 aux besoins avec les plus grandes fractions
            foreach ($indices as $idx) {
                if ($reste <= 0) break;
                // Ne pas dépasser le besoin demandé
                if ($allocations[$idx]['plancher'] < (int) $allocations[$idx]['besoin_qte']) {
                    $allocations[$idx]['plancher'] += 1;
                    $reste--;
                }
            }

            // 3. Insérer les dispatches en drainant les don_details
            $dd_list = &$details_par_type[$type_id];
            $dd_index = 0;

            foreach ($allocations as $alloc) {
                $reste_alloc = $alloc['plancher'];
                if ($reste_alloc <= 0) continue;

                while ($reste_alloc > 0 && $dd_index < count($dd_list)) {
                    $a_prendre = min($reste_alloc, $dd_list[$dd_index]['restant']);

                    if ($a_prendre > 0) {
                        $db->runQuery(
                            "INSERT INTO dispatch (don_detail_id, besoin_id, quantite) VALUES (?, ?, ?)",
                            [$dd_list[$dd_index]['dd_id'], $alloc['besoin_id'], $a_prendre]
                        );
                        $reste_alloc -= $a_prendre;
                        $dd_list[$dd_index]['restant'] -= $a_prendre;
                    }

                    if ($dd_list[$dd_index]['restant'] <= 0) {
                        $dd_index++;
                    }
                }
            }
        }
    }
}
