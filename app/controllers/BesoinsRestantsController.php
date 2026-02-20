<?php

namespace app\controllers;

use flight\Engine;

class BesoinsRestantsController
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    /**
     * Afficher les besoins restants (non satisfaits)
     */
    public function index(): void
    {
        $db = $this->app->db();
        $ville_id = (int) ($this->app->request()->query->ville_id ?? 0);

        // Récupérer les villes pour le filtre
        $villes = $db->fetchAll("
            SELECT v.id, v.nom, r.nom AS region_nom 
            FROM ville v 
            JOIN region r ON v.region_id = r.id 
            ORDER BY r.nom, v.nom
        ");

        // Construire la requête des besoins restants
        $sql = "
            SELECT b.*, 
                   v.nom AS ville_nom, 
                   r.nom AS region_nom,
                   tb.nom AS type_nom, tb.categorie, tb.prix_unitaire,
                   COALESCE(dispatched.total, 0) AS quantite_dispatchee,
                   COALESCE(achetee.total, 0) AS quantite_achetee,
                   (b.quantite - COALESCE(dispatched.total, 0) - COALESCE(achetee.total, 0)) AS quantite_restante,
                   ((b.quantite - COALESCE(dispatched.total, 0) - COALESCE(achetee.total, 0)) * tb.prix_unitaire) AS valeur_restante
            FROM besoin b
            JOIN ville v ON b.ville_id = v.id
            JOIN region r ON v.region_id = r.id
            JOIN type_besoin tb ON b.type_besoin_id = tb.id
            LEFT JOIN (
                SELECT besoin_id, SUM(quantite) AS total
                FROM dispatch
                GROUP BY besoin_id
            ) dispatched ON dispatched.besoin_id = b.id
            LEFT JOIN (
                SELECT besoin_id, SUM(quantite) AS total
                FROM achat
                GROUP BY besoin_id
            ) achetee ON achetee.besoin_id = b.id
            HAVING quantite_restante > 0
        ";
        $params = [];

        if ($ville_id > 0) {
            $sql = "
                SELECT b.*, 
                       v.nom AS ville_nom, 
                       r.nom AS region_nom,
                       tb.nom AS type_nom, tb.categorie, tb.prix_unitaire,
                       COALESCE(dispatched.total, 0) AS quantite_dispatchee,
                       COALESCE(achetee.total, 0) AS quantite_achetee,
                       (b.quantite - COALESCE(dispatched.total, 0) - COALESCE(achetee.total, 0)) AS quantite_restante,
                       ((b.quantite - COALESCE(dispatched.total, 0) - COALESCE(achetee.total, 0)) * tb.prix_unitaire) AS valeur_restante
                FROM besoin b
                JOIN ville v ON b.ville_id = v.id
                JOIN region r ON v.region_id = r.id
                JOIN type_besoin tb ON b.type_besoin_id = tb.id
                LEFT JOIN (
                    SELECT besoin_id, SUM(quantite) AS total
                    FROM dispatch
                    GROUP BY besoin_id
                ) dispatched ON dispatched.besoin_id = b.id
                LEFT JOIN (
                    SELECT besoin_id, SUM(quantite) AS total
                    FROM achat
                    GROUP BY besoin_id
                ) achetee ON achetee.besoin_id = b.id
                WHERE b.ville_id = ?
                HAVING quantite_restante > 0
                ORDER BY b.date_saisie DESC
            ";
            $params[] = $ville_id;
        } else {
            $sql .= " ORDER BY b.date_saisie DESC";
        }

        $besoins = $db->fetchAll($sql, $params);

        // Total des besoins restants
        $total_restant = 0;
        foreach ($besoins as $b) {
            $total_restant += (float) $b['valeur_restante'];
        }

        // Vérifier s'il y a des dons en argent disponibles
        $dons_argent_disponibles = (float) $db->fetchField("
            SELECT COALESCE(SUM(dd.quantite - COALESCE(used.total_used, 0)), 0)
            FROM don_detail dd
            JOIN type_besoin tb ON dd.type_besoin_id = tb.id
            LEFT JOIN (
                SELECT don_detail_id, SUM(montant_total) AS total_used
                FROM achat
                GROUP BY don_detail_id
            ) used ON used.don_detail_id = dd.id
            WHERE tb.categorie = 'argent'
        ");

        $this->app->render('besoins-restants/index', [
            'page_title'               => 'Besoins restants',
            'active_menu'              => 'besoins-restants',
            'besoins'                  => $besoins,
            'villes'                   => $villes,
            'ville_id'                 => $ville_id,
            'total_restant'            => $total_restant,
            'dons_argent_disponibles'  => $dons_argent_disponibles,
        ]);
    }
}
