<?php

namespace app\logic;

use flight\database\PdoWrapper;

/**
 * Logique de dispatch automatique des dons vers les besoins.
 * Le dispatch se fait par ordre chronologique (date du don, puis date du besoin).
 */
class DispatchLogic
{
    /**
     * Exécuter le dispatch automatique.
     * Supprime les anciens dispatches et recalcule tout.
     */
    public static function executer(PdoWrapper $db): void
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

        // 3. Pour chaque détail de don, attribuer aux besoins correspondants
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
                HAVING (b.quantite - deja_dispatche) > 0
                ORDER BY b.date_saisie ASC, b.id ASC
            ", [(int) $dd['type_besoin_id']]);

            foreach ($besoins as $besoin) {
                if ($reste_don <= 0) {
                    break;
                }

                $besoin_restant = (float) $besoin['besoin_qte'] - (float) $besoin['deja_dispatche'];
                $a_dispatcher   = min($reste_don, $besoin_restant);

                if ($a_dispatcher > 0) {
                    $db->runQuery(
                        "INSERT INTO dispatch (don_detail_id, besoin_id, quantite) VALUES (?, ?, ?)",
                        [(int) $dd['dd_id'], (int) $besoin['besoin_id'], $a_dispatcher]
                    );
                    $reste_don -= $a_dispatcher;
                }
            }
        }
    }
}
