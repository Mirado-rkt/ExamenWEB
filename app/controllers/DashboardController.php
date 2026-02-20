<?php

namespace app\controllers;

use app\logic\DispatchLogic;
use flight\Engine;

class DashboardController
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    public function index(): void
    {
        $db = $this->app->db();

        // Statistiques globales
        $stats = [
            'regions'  => (int) $db->fetchField("SELECT COUNT(*) FROM region"),
            'villes'   => (int) $db->fetchField("SELECT COUNT(*) FROM ville"),
            'nb_dons'  => (int) $db->fetchField("SELECT COUNT(*) FROM don"),
            'nb_besoins' => (int) $db->fetchField("SELECT COUNT(*) FROM besoin"),
        ];

        // Vue par ville : besoins détaillés ET dons attribués détaillés
        $villes_data = $db->fetchAll("
            SELECT 
                v.id,
                v.nom AS ville,
                r.nom AS region
            FROM ville v
            JOIN region r ON v.region_id = r.id
            ORDER BY r.nom, v.nom
        ");

        $villes = [];
        foreach ($villes_data as $v) {
            $vid = (int) $v['id'];

            // Besoins de cette ville (par type, avec quantité)
            $besoins = $db->fetchAll("
                SELECT tb.nom AS type_nom, tb.categorie, tb.prix_unitaire,
                       SUM(b.quantite) AS quantite
                FROM besoin b
                JOIN type_besoin tb ON b.type_besoin_id = tb.id
                WHERE b.ville_id = ?
                GROUP BY tb.id, tb.nom, tb.categorie, tb.prix_unitaire
                ORDER BY tb.categorie, tb.nom
            ", [$vid]);

            // Dons attribués à cette ville (par type, avec quantité dispatchée)
            $dons_attribues = $db->fetchAll("
                SELECT tb.nom AS type_nom, tb.categorie,
                       SUM(dp.quantite) AS quantite
                FROM dispatch dp
                JOIN besoin b ON dp.besoin_id = b.id
                JOIN type_besoin tb ON b.type_besoin_id = tb.id
                WHERE b.ville_id = ?
                GROUP BY tb.id, tb.nom, tb.categorie
                ORDER BY tb.categorie, tb.nom
            ", [$vid]);

            if (!empty($besoins)) {
                $v['besoins']        = $besoins;
                $v['dons_attribues'] = $dons_attribues;
                $villes[] = $v;
            }
        }

        $this->app->render('dashboard', [
            'page_title'  => 'Tableau de bord',
            'active_menu' => 'dashboard',
            'stats'       => $stats,
            'villes'      => $villes,
        ]);
    }

    /**
     * Réinitialiser TOUTES les données à l'état original
     */
    public function reinitialiser(): void
    {
        $db = $this->app->db();

        // Utilise la même méthode que DispatchController (TRUNCATE + re-insert)
        \app\controllers\DispatchController::insererDonneesOriginalesStatic($db);

        flash('success', 'Réinitialisation complète effectuée : toutes les données ont été restaurées à l\'état original.');
        $this->app->redirect(base_url('/'));
    }
}
