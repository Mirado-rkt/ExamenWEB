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
        $simulation_mode = $this->app->request()->query->simulation ?? false;
        $current_mode = $_SESSION['dispatch_mode'] ?? DispatchLogic::MODE_PRIORITAIRE;

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
                   b.date_saisie AS date_besoin,
                   v.nom AS ville_nom,
                   tb.nom AS type_nom, tb.categorie, tb.prix_unitaire,
                   d.donateur,
                   d.date_don,
                   (dp.quantite * tb.prix_unitaire) AS valeur
            FROM dispatch dp
            JOIN besoin b ON dp.besoin_id = b.id
            JOIN ville v ON b.ville_id = v.id
            JOIN type_besoin tb ON b.type_besoin_id = tb.id
            JOIN don_detail dd ON dp.don_detail_id = dd.id
            JOIN don d ON dd.don_id = d.id
            ORDER BY b.date_saisie ASC, dp.id ASC
            LIMIT 50
        ");

        $nb_dispatches = (int) $db->fetchField("SELECT COUNT(*) FROM dispatch");

        $this->app->render('dispatch/index', [
            'page_title'      => 'Dispatch des dons',
            'active_menu'     => 'dispatch',
            'villes'          => $villes,
            'dispatches'      => $dispatches,
            'nb_dispatches'   => $nb_dispatches,
            'simulation_mode' => $simulation_mode,
            'current_mode'    => $current_mode,
        ]);
    }

    /**
     * Simuler le dispatch : recalculer sans message de validation
     */
    public function simuler(): void
    {
        $db = $this->app->db();
        $mode = $_POST['mode'] ?? DispatchLogic::MODE_PRIORITAIRE;
        $_SESSION['dispatch_mode'] = $mode;

        // Exécuter le dispatch (recalcul complet) avec le mode choisi
        DispatchLogic::executer($db, $mode);

        $mode_labels = [
            'prioritaire'    => 'Prioritaire (ordre de saisie ASC)',
            'minoritaire'    => 'Minoritaire (ordre de saisie DESC)',
            'proportionnelle' => 'Proportionnelle',
        ];
        $mode_label = $mode_labels[$mode] ?? $mode;

        flash('success', 'Simulation du dispatch effectuée en mode ' . $mode_label . '. Vérifiez les résultats ci-dessous, puis cliquez sur Valider le dispatch pour confirmer.');
        $this->app->redirect(base_url('/dispatch?simulation=1'));
    }

    /**
     * Initialiser le dispatch (supprimer uniquement les dispatches et achats)
     */
    public function initialiser(): void
    {
        $db = $this->app->db();

        // Supprimer tous les dispatches et achats
        $db->runQuery("DELETE FROM dispatch");
        $db->runQuery("DELETE FROM achat");

        flash('success', 'Initialisation du dispatch effectuée : tous les dispatches et achats ont été supprimés.');
        $this->app->redirect(base_url('/dispatch'));
    }

    /**
     * Réinitialiser TOUTES les données à l'état original (SQL de départ)
     */
    public function reinitialiser(): void
    {
        $db = $this->app->db();
        self::insererDonneesOriginalesStatic($db);

        flash('success', 'Réinitialisation complète effectuée : toutes les données ont été restaurées à l\'état original.');
        $this->app->redirect(base_url('/dispatch'));
    }

    /**
     * Valider le dispatch : confirmer l'application définitive
     */
    public function valider(): void
    {
        $db = $this->app->db();
        $mode = $_POST['mode'] ?? ($_SESSION['dispatch_mode'] ?? DispatchLogic::MODE_PRIORITAIRE);
        $_SESSION['dispatch_mode'] = $mode;

        // Exécuter le dispatch (confirmer) avec le mode choisi
        DispatchLogic::executer($db, $mode);

        flash('success', 'Le dispatch a été validé et appliqué avec succès.');
        $this->app->redirect(base_url('/dispatch'));
    }

    /**
     * Réinitialiser TOUTES les tables et réinsérer les données originales.
     * Ultra-optimisé : DELETE au lieu de TRUNCATE + désactivation des checks/index.
     */
    public static function insererDonneesOriginalesStatic(\flight\database\PdoWrapper $db): void
    {
        $sql = "
            SET FOREIGN_KEY_CHECKS=0, UNIQUE_CHECKS=0, AUTOCOMMIT=0;
            DELETE FROM dispatch;
            DELETE FROM achat;
            DELETE FROM don_detail;
            DELETE FROM don;
            DELETE FROM besoin;
            DELETE FROM type_besoin;
            DELETE FROM ville;
            DELETE FROM region;
            ALTER TABLE dispatch AUTO_INCREMENT=1;
            ALTER TABLE achat AUTO_INCREMENT=1;
            ALTER TABLE don_detail AUTO_INCREMENT=1;
            ALTER TABLE don AUTO_INCREMENT=1;
            ALTER TABLE besoin AUTO_INCREMENT=1;
            ALTER TABLE type_besoin AUTO_INCREMENT=1;
            ALTER TABLE ville AUTO_INCREMENT=1;
            ALTER TABLE region AUTO_INCREMENT=1;
            INSERT INTO region (id,nom) VALUES(1,'Analamanga'),(2,'Vakinankaratra'),(3,'Atsinanana'),(4,'Boeny'),(5,'Atsimo-Andrefana');
            INSERT INTO ville (id,nom,region_id) VALUES(1,'Antananarivo',1),(2,'Ambohidratrimo',1),(3,'Antsirabe',2),(4,'Ambatolampy',2),(5,'Toamasina',3),(6,'Mahajanga',4),(7,'Toliara',5);
            INSERT INTO type_besoin (id,nom,categorie,prix_unitaire) VALUES(1,'Riz (kg)','nature',2500),(2,'Huile (litre)','nature',8000),(3,'Sucre (kg)','nature',4000),(4,'Eau (litre)','nature',1000),(5,'Lait en poudre (boîte)','nature',12000),(6,'Tôle (unité)','materiau',35000),(7,'Clou (kg)','materiau',12000),(8,'Bois (unité)','materiau',15000),(9,'Ciment (sac)','materiau',45000),(10,'Bâche (unité)','materiau',25000),(11,'Argent (Ar)','argent',1);
            INSERT INTO besoin (id,ville_id,type_besoin_id,quantite,date_saisie) VALUES(1,1,1,10,'2026-02-01 08:00:00'),(2,1,2,8,'2026-02-01 09:00:00'),(3,3,1,30,'2026-02-02 08:00:00'),(4,3,6,10,'2026-02-02 09:00:00'),(5,5,1,20,'2026-02-03 08:00:00'),(6,5,2,12,'2026-02-03 09:00:00'),(7,6,1,15,'2026-02-04 08:00:00'),(8,2,3,15,'2026-02-04 09:00:00'),(9,4,6,6,'2026-02-05 08:00:00'),(10,7,1,25,'2026-02-05 09:00:00');
            INSERT INTO don (id,donateur,description,date_don) VALUES(1,'Croix-Rouge Madagascar','Don alimentaire d''urgence','2026-02-08 10:00:00'),(2,'UNICEF','Matériaux de reconstruction','2026-02-09 14:00:00'),(3,'Communauté locale','Collecte alimentaire','2026-02-10 11:00:00'),(4,'Gouvernement','Aide financière d''urgence','2026-02-11 09:00:00');
            INSERT INTO don_detail (id,don_id,type_besoin_id,quantite) VALUES(1,1,1,50),(2,1,2,15),(3,2,6,16),(4,2,3,20),(5,3,1,10),(6,4,11,3000000);
            INSERT INTO achat (id,besoin_id,don_detail_id,quantite,prix_unitaire,frais_pourcent,montant_total,date_achat) VALUES(1,1,6,5,2500,10,13750,'2026-02-12 10:00:00'),(2,4,6,3,35000,10,115500,'2026-02-12 11:00:00'),(3,9,6,2,35000,10,77000,'2026-02-12 14:00:00');
            COMMIT;
            SET FOREIGN_KEY_CHECKS=1, UNIQUE_CHECKS=1, AUTOCOMMIT=1;
        ";
        $db->exec($sql);
    }
}
