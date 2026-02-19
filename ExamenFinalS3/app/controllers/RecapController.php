<?php

namespace app\controllers;

use flight\Engine;

class RecapController
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    /**
     * Afficher la page de récapitulation
     */
    public function index(): void
    {
        $db = $this->app->db();

        // 1. Total des besoins (en montant)
        $total_besoins = (float) $db->fetchField("
            SELECT COALESCE(SUM(b.quantite * tb.prix_unitaire), 0)
            FROM besoin b
            JOIN type_besoin tb ON b.type_besoin_id = tb.id
        ");

        // 2. Total satisfait par dispatch direct (dons en nature/matériaux)
        $total_dispatch = (float) $db->fetchField("
            SELECT COALESCE(SUM(dp.quantite * tb.prix_unitaire), 0)
            FROM dispatch dp
            JOIN besoin b ON dp.besoin_id = b.id
            JOIN type_besoin tb ON b.type_besoin_id = tb.id
        ");

        // 3. Total satisfait par achats (dons en argent convertis)
        $total_achats = (float) $db->fetchField("
            SELECT COALESCE(SUM(a.quantite * a.prix_unitaire), 0)
            FROM achat a
        ");

        // Total satisfait (dispatch + achats)
        $total_satisfait = $total_dispatch + $total_achats;

        // 4. Besoins restants
        $total_restant = $total_besoins - $total_satisfait;
        if ($total_restant < 0) {
            $total_restant = 0;
        }

        // 5. Taux de couverture
        $taux_couverture = $total_besoins > 0 ? round(($total_satisfait / $total_besoins) * 100, 1) : 0;

        // 6. Détail par type de besoin
        $details_types = $db->fetchAll("
            SELECT 
                tb.id, tb.nom, tb.categorie, tb.prix_unitaire,
                COALESCE(besoins.total_qte, 0) AS total_besoin_qte,
                COALESCE(besoins.total_valeur, 0) AS total_besoin_valeur,
                COALESCE(dispatched.total_qte, 0) AS total_dispatch_qte,
                COALESCE(dispatched.total_valeur, 0) AS total_dispatch_valeur,
                COALESCE(achete.total_qte, 0) AS total_achat_qte,
                COALESCE(achete.total_valeur, 0) AS total_achat_valeur
            FROM type_besoin tb
            LEFT JOIN (
                SELECT b.type_besoin_id, SUM(b.quantite) AS total_qte, SUM(b.quantite * tb2.prix_unitaire) AS total_valeur
                FROM besoin b
                JOIN type_besoin tb2 ON b.type_besoin_id = tb2.id
                GROUP BY b.type_besoin_id
            ) besoins ON besoins.type_besoin_id = tb.id
            LEFT JOIN (
                SELECT b.type_besoin_id, SUM(dp.quantite) AS total_qte, SUM(dp.quantite * tb2.prix_unitaire) AS total_valeur
                FROM dispatch dp
                JOIN besoin b ON dp.besoin_id = b.id
                JOIN type_besoin tb2 ON b.type_besoin_id = tb2.id
                GROUP BY b.type_besoin_id
            ) dispatched ON dispatched.type_besoin_id = tb.id
            LEFT JOIN (
                SELECT b.type_besoin_id, SUM(a.quantite) AS total_qte, SUM(a.quantite * a.prix_unitaire) AS total_valeur
                FROM achat a
                JOIN besoin b ON a.besoin_id = b.id
                GROUP BY b.type_besoin_id
            ) achete ON achete.type_besoin_id = tb.id
            WHERE besoins.total_qte > 0
            ORDER BY tb.categorie, tb.nom
        ");

        // 7. Détail par ville
        $details_villes = $db->fetchAll("
            SELECT 
                v.id, v.nom AS ville, r.nom AS region,
                COALESCE(besoins.total_valeur, 0) AS total_besoin,
                COALESCE(dispatched.total_valeur, 0) AS total_dispatch,
                COALESCE(achete.total_valeur, 0) AS total_achat
            FROM ville v
            JOIN region r ON v.region_id = r.id
            LEFT JOIN (
                SELECT b.ville_id, SUM(b.quantite * tb.prix_unitaire) AS total_valeur
                FROM besoin b
                JOIN type_besoin tb ON b.type_besoin_id = tb.id
                GROUP BY b.ville_id
            ) besoins ON besoins.ville_id = v.id
            LEFT JOIN (
                SELECT b.ville_id, SUM(dp.quantite * tb.prix_unitaire) AS total_valeur
                FROM dispatch dp
                JOIN besoin b ON dp.besoin_id = b.id
                JOIN type_besoin tb ON b.type_besoin_id = tb.id
                GROUP BY b.ville_id
            ) dispatched ON dispatched.ville_id = v.id
            LEFT JOIN (
                SELECT b.ville_id, SUM(a.quantite * a.prix_unitaire) AS total_valeur
                FROM achat a
                JOIN besoin b ON a.besoin_id = b.id
                GROUP BY b.ville_id
            ) achete ON achete.ville_id = v.id
            WHERE besoins.total_valeur > 0
            ORDER BY r.nom, v.nom
        ");

        $this->app->render('recap/index', [
            'page_title'       => 'Récapitulation',
            'active_menu'      => 'recap',
            'total_besoins'    => $total_besoins,
            'total_dispatch'   => $total_dispatch,
            'total_achats'     => $total_achats,
            'total_satisfait'  => $total_satisfait,
            'total_restant'    => $total_restant,
            'taux_couverture'  => $taux_couverture,
            'details_types'    => $details_types,
            'details_villes'   => $details_villes,
        ]);
    }

    /**
     * API JSON pour récupérer les données de récapitulation (appel Ajax)
     */
    public function getData(): void
    {
        $db = $this->app->db();

        // 1. Total des besoins (en montant)
        $total_besoins = (float) $db->fetchField("
            SELECT COALESCE(SUM(b.quantite * tb.prix_unitaire), 0)
            FROM besoin b
            JOIN type_besoin tb ON b.type_besoin_id = tb.id
        ");

        // 2. Total satisfait par dispatch direct (dons en nature/matériaux)
        $total_dispatch = (float) $db->fetchField("
            SELECT COALESCE(SUM(dp.quantite * tb.prix_unitaire), 0)
            FROM dispatch dp
            JOIN besoin b ON dp.besoin_id = b.id
            JOIN type_besoin tb ON b.type_besoin_id = tb.id
        ");

        // 3. Total satisfait par achats (dons en argent convertis)
        $total_achats = (float) $db->fetchField("
            SELECT COALESCE(SUM(a.quantite * a.prix_unitaire), 0)
            FROM achat a
        ");

        // Total satisfait (dispatch + achats)
        $total_satisfait = $total_dispatch + $total_achats;

        // 4. Besoins restants
        $total_restant = $total_besoins - $total_satisfait;
        if ($total_restant < 0) {
            $total_restant = 0;
        }

        // 5. Taux de couverture
        $taux_couverture = $total_besoins > 0 ? round(($total_satisfait / $total_besoins) * 100, 1) : 0;

        // 6. Détail par type de besoin
        $details_types = $db->fetchAll("
            SELECT 
                tb.id, tb.nom, tb.categorie, tb.prix_unitaire,
                COALESCE(besoins.total_qte, 0) AS total_besoin_qte,
                COALESCE(besoins.total_valeur, 0) AS total_besoin_valeur,
                COALESCE(dispatched.total_qte, 0) AS total_dispatch_qte,
                COALESCE(dispatched.total_valeur, 0) AS total_dispatch_valeur,
                COALESCE(achete.total_qte, 0) AS total_achat_qte,
                COALESCE(achete.total_valeur, 0) AS total_achat_valeur
            FROM type_besoin tb
            LEFT JOIN (
                SELECT b.type_besoin_id, SUM(b.quantite) AS total_qte, SUM(b.quantite * tb2.prix_unitaire) AS total_valeur
                FROM besoin b
                JOIN type_besoin tb2 ON b.type_besoin_id = tb2.id
                GROUP BY b.type_besoin_id
            ) besoins ON besoins.type_besoin_id = tb.id
            LEFT JOIN (
                SELECT b.type_besoin_id, SUM(dp.quantite) AS total_qte, SUM(dp.quantite * tb2.prix_unitaire) AS total_valeur
                FROM dispatch dp
                JOIN besoin b ON dp.besoin_id = b.id
                JOIN type_besoin tb2 ON b.type_besoin_id = tb2.id
                GROUP BY b.type_besoin_id
            ) dispatched ON dispatched.type_besoin_id = tb.id
            LEFT JOIN (
                SELECT b.type_besoin_id, SUM(a.quantite) AS total_qte, SUM(a.quantite * a.prix_unitaire) AS total_valeur
                FROM achat a
                JOIN besoin b ON a.besoin_id = b.id
                GROUP BY b.type_besoin_id
            ) achete ON achete.type_besoin_id = tb.id
            WHERE besoins.total_qte > 0
            ORDER BY tb.categorie, tb.nom
        ");

        // 7. Détail par ville
        $details_villes = $db->fetchAll("
            SELECT 
                v.id, v.nom AS ville, r.nom AS region,
                COALESCE(besoins.total_valeur, 0) AS total_besoin,
                COALESCE(dispatched.total_valeur, 0) AS total_dispatch,
                COALESCE(achete.total_valeur, 0) AS total_achat
            FROM ville v
            JOIN region r ON v.region_id = r.id
            LEFT JOIN (
                SELECT b.ville_id, SUM(b.quantite * tb.prix_unitaire) AS total_valeur
                FROM besoin b
                JOIN type_besoin tb ON b.type_besoin_id = tb.id
                GROUP BY b.ville_id
            ) besoins ON besoins.ville_id = v.id
            LEFT JOIN (
                SELECT b.ville_id, SUM(dp.quantite * tb.prix_unitaire) AS total_valeur
                FROM dispatch dp
                JOIN besoin b ON dp.besoin_id = b.id
                JOIN type_besoin tb ON b.type_besoin_id = tb.id
                GROUP BY b.ville_id
            ) dispatched ON dispatched.ville_id = v.id
            LEFT JOIN (
                SELECT b.ville_id, SUM(a.quantite * a.prix_unitaire) AS total_valeur
                FROM achat a
                JOIN besoin b ON a.besoin_id = b.id
                GROUP BY b.ville_id
            ) achete ON achete.ville_id = v.id
            WHERE besoins.total_valeur > 0
            ORDER BY r.nom, v.nom
        ");

        // Renvoyer les données en JSON
        $this->app->json([
            'success' => true,
            'data' => [
                'total_besoins'    => $total_besoins,
                'total_dispatch'   => $total_dispatch,
                'total_achats'     => $total_achats,
                'total_satisfait'  => $total_satisfait,
                'total_restant'    => $total_restant,
                'taux_couverture'  => $taux_couverture,
                'details_types'    => $details_types,
                'details_villes'   => $details_villes,
            ],
            'timestamp' => date('d/m/Y H:i:s'),
        ]);
    }
}
