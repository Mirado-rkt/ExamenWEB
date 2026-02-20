<?php

namespace app\controllers;

use flight\Engine;

class AchatController
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    /**
     * Liste des achats avec filtre par ville
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

        // Construire la requête des achats avec filtre optionnel
        $sql = "
            SELECT a.*, 
                   b.quantite AS besoin_qte,
                   v.nom AS ville_nom,
                   r.nom AS region_nom,
                   tb.nom AS type_nom, tb.categorie,
                   d.donateur
            FROM achat a
            JOIN besoin b ON a.besoin_id = b.id
            JOIN ville v ON b.ville_id = v.id
            JOIN region r ON v.region_id = r.id
            JOIN type_besoin tb ON b.type_besoin_id = tb.id
            JOIN don_detail dd ON a.don_detail_id = dd.id
            JOIN don d ON dd.don_id = d.id
        ";
        $params = [];

        if ($ville_id > 0) {
            $sql .= " WHERE b.ville_id = ?";
            $params[] = $ville_id;
        }

        $sql .= " ORDER BY a.date_achat DESC";
        $achats = $db->fetchAll($sql, $params);

        // Total des achats
        $total_achats = 0;
        foreach ($achats as $a) {
            $total_achats += (float) $a['montant_total'];
        }

        $this->app->render('achats/index', [
            'page_title'    => 'Liste des achats',
            'active_menu'   => 'achats',
            'achats'        => $achats,
            'villes'        => $villes,
            'ville_id'      => $ville_id,
            'total_achats'  => $total_achats,
        ]);
    }

    /**
     * Afficher le formulaire d'achat pour un besoin spécifique
     */
    public function create(): void
    {
        $db = $this->app->db();
        $besoin_id = (int) ($this->app->request()->query->besoin_id ?? 0);

        if ($besoin_id === 0) {
            flash('error', 'Aucun besoin spécifié.');
            $this->app->redirect(base_url('/besoins-restants'));
            return;
        }

        // Récupérer le besoin avec ses infos
        $besoin = $db->fetchRow("
            SELECT b.*, v.nom AS ville_nom, r.nom AS region_nom,
                   tb.nom AS type_nom, tb.categorie, tb.prix_unitaire,
                   (b.quantite * tb.prix_unitaire) AS valeur_totale
            FROM besoin b
            JOIN ville v ON b.ville_id = v.id
            JOIN region r ON v.region_id = r.id
            JOIN type_besoin tb ON b.type_besoin_id = tb.id
            WHERE b.id = ?
        ", [$besoin_id]);

        if (!$besoin) {
            flash('error', 'Besoin introuvable.');
            $this->app->redirect(base_url('/besoins-restants'));
            return;
        }

        // Vérifier si c'est un besoin en nature ou matériaux (pas argent)
        if ($besoin['categorie'] === 'argent') {
            flash('error', 'Les besoins en argent ne peuvent pas être achetés.');
            $this->app->redirect(base_url('/besoins-restants'));
            return;
        }

        // Calculer la quantité restante (besoin - dispatch - achats)
        $quantite_dispatchee = (float) $db->fetchField("
            SELECT COALESCE(SUM(quantite), 0) FROM dispatch WHERE besoin_id = ?
        ", [$besoin_id]);

        $quantite_achetee = (float) $db->fetchField("
            SELECT COALESCE(SUM(quantite), 0) FROM achat WHERE besoin_id = ?
        ", [$besoin_id]);

        $quantite_restante = (float) $besoin['quantite'] - $quantite_dispatchee - $quantite_achetee;

        if ($quantite_restante <= 0) {
            flash('error', 'Ce besoin est déjà entièrement satisfait.');
            $this->app->redirect(base_url('/besoins-restants'));
            return;
        }

        // Vérifier si ce type de besoin existe encore dans les dons restants (nature/matériaux)
        $total_dons_type = (float) $db->fetchField("
            SELECT COALESCE(SUM(dd.quantite), 0)
            FROM don_detail dd
            JOIN type_besoin tb ON dd.type_besoin_id = tb.id
            WHERE dd.type_besoin_id = ? AND tb.categorie != 'argent'
        ", [(int) $besoin['type_besoin_id']]);

        $total_dispatche_type = (float) $db->fetchField("
            SELECT COALESCE(SUM(dp.quantite), 0)
            FROM dispatch dp
            JOIN don_detail dd ON dp.don_detail_id = dd.id
            WHERE dd.type_besoin_id = ?
        ", [(int) $besoin['type_besoin_id']]);

        $don_restant_type = $total_dons_type - $total_dispatche_type;

        // Si des dons de ce type sont encore disponibles, bloquer l'achat
        if ($don_restant_type > 0) {
            flash('error', 'Impossible d\'acheter : Ce type de besoin (' . $besoin['type_nom'] . ') est encore disponible dans les dons en nature/matériaux (' . format_nb($don_restant_type) . ' unités restantes). Exécutez d\'abord le dispatch pour utiliser les dons directs.');
            $this->app->redirect(base_url('/besoins-restants'));
            return;
        }

        // Récupérer les dons en argent disponibles (non entièrement utilisés)
        $dons_argent = $db->fetchAll("
            SELECT dd.id AS don_detail_id, dd.quantite AS montant_don,
                   d.donateur, d.date_don,
                   COALESCE(used.total_used, 0) AS montant_utilise,
                   (dd.quantite - COALESCE(used.total_used, 0)) AS montant_disponible
            FROM don_detail dd
            JOIN don d ON dd.don_id = d.id
            JOIN type_besoin tb ON dd.type_besoin_id = tb.id
            LEFT JOIN (
                SELECT don_detail_id, SUM(montant_total) AS total_used
                FROM achat
                GROUP BY don_detail_id
            ) used ON used.don_detail_id = dd.id
            WHERE tb.categorie = 'argent'
            HAVING montant_disponible > 0
            ORDER BY d.date_don ASC
        ");

        // Charger la configuration des frais
        $config = require __DIR__ . '/../config/config.php';
        $frais_pourcent = $config['achat']['frais_pourcent'] ?? 10;

        $this->app->render('achats/form', [
            'page_title'        => 'Nouvel achat',
            'active_menu'       => 'achats',
            'besoin'            => $besoin,
            'quantite_restante' => $quantite_restante,
            'dons_argent'       => $dons_argent,
            'frais_pourcent'    => $frais_pourcent,
        ]);
    }

    /**
     * Enregistrer un achat
     */
    public function store(): void
    {
        $data = $this->app->request()->data;
        $db = $this->app->db();

        $besoin_id = (int) ($data->besoin_id ?? 0);
        $don_detail_id = (int) ($data->don_detail_id ?? 0);
        $quantite = (float) ($data->quantite ?? 0);

        if ($besoin_id === 0 || $don_detail_id === 0 || $quantite <= 0) {
            flash('error', 'Tous les champs sont requis.');
            $this->app->redirect(base_url('/achats/create?besoin_id=' . $besoin_id));
            return;
        }

        // Récupérer les infos du besoin
        $besoin = $db->fetchRow("
            SELECT b.*, tb.prix_unitaire, tb.categorie, tb.nom AS type_nom
            FROM besoin b
            JOIN type_besoin tb ON b.type_besoin_id = tb.id
            WHERE b.id = ?
        ", [$besoin_id]);

        if (!$besoin || $besoin['categorie'] === 'argent') {
            flash('error', 'Besoin invalide pour un achat.');
            $this->app->redirect(base_url('/besoins-restants'));
            return;
        }

        // Vérifier la quantité restante du besoin
        $quantite_dispatchee = (float) $db->fetchField("
            SELECT COALESCE(SUM(quantite), 0) FROM dispatch WHERE besoin_id = ?
        ", [$besoin_id]);

        $quantite_achetee = (float) $db->fetchField("
            SELECT COALESCE(SUM(quantite), 0) FROM achat WHERE besoin_id = ?
        ", [$besoin_id]);

        $quantite_restante = (float) $besoin['quantite'] - $quantite_dispatchee - $quantite_achetee;

        if ($quantite > $quantite_restante) {
            flash('error', 'La quantité demandée dépasse la quantité restante du besoin.');
            $this->app->redirect(base_url('/achats/create?besoin_id=' . $besoin_id));
            return;
        }

        // Vérifier si ce type de besoin existe encore dans les dons restants (nature/matériaux)
        // On calcule : total des dons de ce type - total dispatché de ce type
        $total_dons_type = (float) $db->fetchField("
            SELECT COALESCE(SUM(dd.quantite), 0)
            FROM don_detail dd
            JOIN type_besoin tb ON dd.type_besoin_id = tb.id
            WHERE dd.type_besoin_id = ? AND tb.categorie != 'argent'
        ", [(int) $besoin['type_besoin_id']]);

        $total_dispatche_type = (float) $db->fetchField("
            SELECT COALESCE(SUM(dp.quantite), 0)
            FROM dispatch dp
            JOIN don_detail dd ON dp.don_detail_id = dd.id
            WHERE dd.type_besoin_id = ?
        ", [(int) $besoin['type_besoin_id']]);

        $don_restant = $total_dons_type - $total_dispatche_type;

        if ($don_restant > 0) {
            flash('error', 'Erreur : Ce type de besoin (' . $besoin['type_nom'] . ') est encore disponible dans les dons en nature/matériaux (' . format_nb($don_restant) . ' unités restantes). Utilisez d\'abord les dons directs via le dispatch avant d\'acheter.');
            $this->app->redirect(base_url('/achats/create?besoin_id=' . $besoin_id));
            return;
        }

        // Charger la configuration des frais
        $config = require __DIR__ . '/../config/config.php';
        $frais_pourcent = $config['achat']['frais_pourcent'] ?? 10;

        // Calculer le montant total avec frais
        $prix_unitaire = (float) $besoin['prix_unitaire'];
        $montant_base = $quantite * $prix_unitaire;
        $montant_total = $montant_base * (1 + $frais_pourcent / 100);

        // Vérifier le montant disponible dans le don en argent
        $don_argent = $db->fetchRow("
            SELECT dd.quantite AS montant_don,
                   COALESCE(used.total_used, 0) AS montant_utilise
            FROM don_detail dd
            LEFT JOIN (
                SELECT don_detail_id, SUM(montant_total) AS total_used
                FROM achat
                GROUP BY don_detail_id
            ) used ON used.don_detail_id = dd.id
            WHERE dd.id = ?
        ", [$don_detail_id]);

        if (!$don_argent) {
            flash('error', 'Don en argent introuvable.');
            $this->app->redirect(base_url('/achats/create?besoin_id=' . $besoin_id));
            return;
        }

        $montant_disponible = (float) $don_argent['montant_don'] - (float) $don_argent['montant_utilise'];

        if ($montant_total > $montant_disponible) {
            flash('error', 'Le montant total de l\'achat (' . format_ar($montant_total) . ') dépasse le montant disponible (' . format_ar($montant_disponible) . ').');
            $this->app->redirect(base_url('/achats/create?besoin_id=' . $besoin_id));
            return;
        }

        // Enregistrer l'achat
        $db->runQuery(
            "INSERT INTO achat (besoin_id, don_detail_id, quantite, prix_unitaire, frais_pourcent, montant_total) VALUES (?, ?, ?, ?, ?, ?)",
            [$besoin_id, $don_detail_id, $quantite, $prix_unitaire, $frais_pourcent, $montant_total]
        );

        flash('success', 'Achat enregistré avec succès. Montant total : ' . format_ar($montant_total) . ' (dont ' . $frais_pourcent . '% de frais).');
        $this->app->redirect(base_url('/achats'));
    }

    /**
     * Supprimer un achat
     */
    public function delete(string $id): void
    {
        $this->app->db()->runQuery("DELETE FROM achat WHERE id = ?", [(int) $id]);
        flash('success', 'Achat supprimé.');
        $this->app->redirect(base_url('/achats'));
    }
}
