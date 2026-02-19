<?php

namespace app\controllers;

use app\logic\DispatchLogic;
use flight\Engine;

class DispatchController
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    /**
     * Afficher l'état actuel du dispatch
     */
    public function index(): void
    {
        $db = $this->app->db();

        // Auto-dispatch
        DispatchLogic::executer($db);

        // Résumé par ville
        $villes = $db->fetchAll("
            SELECT 
                v.id, v.nom AS ville, r.nom AS region,
                COALESCE(bs.total_besoin, 0) AS total_besoin,
                COALESCE(ds.total_dispatch, 0) AS total_dispatch
            FROM ville v
            JOIN region r ON v.region_id = r.id
            LEFT JOIN (
                SELECT b.ville_id, SUM(b.quantite * tb.prix_unitaire) AS total_besoin
                FROM besoin b
                JOIN type_besoin tb ON b.type_besoin_id = tb.id
                GROUP BY b.ville_id
            ) bs ON bs.ville_id = v.id
            LEFT JOIN (
                SELECT b.ville_id, SUM(dp.quantite * tb.prix_unitaire) AS total_dispatch
                FROM dispatch dp
                JOIN besoin b ON dp.besoin_id = b.id
                JOIN type_besoin tb ON b.type_besoin_id = tb.id
                GROUP BY b.ville_id
            ) ds ON ds.ville_id = v.id
            WHERE bs.total_besoin > 0
            ORDER BY v.nom
        ");

        // Détails des dispatches récents
        $dispatches = $db->fetchAll("
            SELECT dp.*, 
                   b.quantite AS besoin_qte,
                   v.nom AS ville_nom,
                   tb.nom AS type_nom, tb.categorie, tb.prix_unitaire,
                   d.donateur,
                   (dp.quantite * tb.prix_unitaire) AS valeur
            FROM dispatch dp
            JOIN besoin b ON dp.besoin_id = b.id
            JOIN ville v ON b.ville_id = v.id
            JOIN type_besoin tb ON b.type_besoin_id = tb.id
            JOIN don_detail dd ON dp.don_detail_id = dd.id
            JOIN don d ON dd.don_id = d.id
            ORDER BY dp.date_dispatch DESC
            LIMIT 50
        ");

        $nb_dispatches = (int) $db->fetchField("SELECT COUNT(*) FROM dispatch");

        $this->app->render('dispatch/index', [
            'page_title'    => 'Dispatch des dons',
            'active_menu'   => 'dispatch',
            'villes'        => $villes,
            'dispatches'    => $dispatches,
            'nb_dispatches' => $nb_dispatches,
        ]);
    }

    /**
     * Simuler le dispatch : attribuer les dons aux besoins par ordre chronologique
     */
    public function simuler(): void
    {
        $db = $this->app->db();

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

            // Récupérer les besoins correspondants (même type), triés par date de saisie
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

        flash('success', 'Simulation du dispatch terminée avec succès.');
        $this->app->redirect('/dispatch');
    }
}
