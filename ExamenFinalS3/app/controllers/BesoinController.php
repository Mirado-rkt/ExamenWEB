<?php

namespace app\controllers;

use app\logic\DispatchLogic;
use flight\Engine;

class BesoinController
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    public function index(): void
    {
        $db = $this->app->db();
        $besoins = $db->fetchAll("
            SELECT b.*, v.nom AS ville_nom, r.nom AS region_nom,
                   tb.nom AS type_nom, tb.categorie, tb.prix_unitaire,
                   (b.quantite * tb.prix_unitaire) AS valeur_totale
            FROM besoin b
            JOIN ville v ON b.ville_id = v.id
            JOIN region r ON v.region_id = r.id
            JOIN type_besoin tb ON b.type_besoin_id = tb.id
            ORDER BY b.date_saisie DESC
        ");

        $this->app->render('besoins/index', [
            'page_title'  => 'Besoins des sinistrés',
            'active_menu' => 'besoins',
            'besoins'     => $besoins,
        ]);
    }

    public function create(): void
    {
        $db     = $this->app->db();
        $villes = $db->fetchAll("SELECT v.id, v.nom, r.nom AS region_nom FROM ville v JOIN region r ON v.region_id = r.id ORDER BY r.nom, v.nom");
        $types  = $db->fetchAll("SELECT * FROM type_besoin ORDER BY categorie, nom");
        $selected_ville_id = (int) ($this->app->request()->query->ville_id ?? 0);

        $this->app->render('besoins/form', [
            'page_title'  => 'Nouveau besoin',
            'active_menu' => 'besoins',
            'besoin'      => null,
            'villes'      => $villes,
            'types'       => $types,
            'selected_ville_id' => $selected_ville_id,
        ]);
    }

    public function store(): void
    {
        $data          = $this->app->request()->data;
        $ville_id      = (int) ($data->ville_id ?? 0);
        $type_besoin_id = (int) ($data->type_besoin_id ?? 0);
        $quantite      = (float) ($data->quantite ?? 0);

        if ($ville_id === 0 || $type_besoin_id === 0 || $quantite <= 0) {
            flash('error', 'Tous les champs sont requis et la quantité doit être positive.');
            $this->app->redirect(base_url('/besoins/create'));
            return;
        }

        $this->app->db()->runQuery(
            "INSERT INTO besoin (ville_id, type_besoin_id, quantite) VALUES (?, ?, ?)",
            [$ville_id, $type_besoin_id, $quantite]
        );
        // Auto-dispatch après ajout d'un besoin
        DispatchLogic::executer($this->app->db());

        flash('success', 'Besoin enregistré avec succès.');
        $this->app->redirect(base_url('/besoins'));
    }

    public function edit(string $id): void
    {
        $db     = $this->app->db();
        $besoin = $db->fetchRow("SELECT * FROM besoin WHERE id = ?", [(int) $id]);
        if (!$besoin) {
            $this->app->halt(404, 'Besoin introuvable');
            return;
        }

        $villes = $db->fetchAll("SELECT v.id, v.nom, r.nom AS region_nom FROM ville v JOIN region r ON v.region_id = r.id ORDER BY r.nom, v.nom");
        $types  = $db->fetchAll("SELECT * FROM type_besoin ORDER BY categorie, nom");

        $this->app->render('besoins/form', [
            'page_title'  => 'Modifier le besoin',
            'active_menu' => 'besoins',
            'besoin'      => $besoin,
            'villes'      => $villes,
            'types'       => $types,
        ]);
    }

    public function update(string $id): void
    {
        $data          = $this->app->request()->data;
        $ville_id      = (int) ($data->ville_id ?? 0);
        $type_besoin_id = (int) ($data->type_besoin_id ?? 0);
        $quantite      = (float) ($data->quantite ?? 0);

        if ($ville_id === 0 || $type_besoin_id === 0 || $quantite <= 0) {
            flash('error', 'Tous les champs sont requis.');
            $this->app->redirect(base_url('/besoins/edit/' . $id));
            return;
        }

        $this->app->db()->runQuery(
            "UPDATE besoin SET ville_id = ?, type_besoin_id = ?, quantite = ? WHERE id = ?",
            [$ville_id, $type_besoin_id, $quantite, (int) $id]
        );
        flash('success', 'Besoin modifié avec succès.');
        $this->app->redirect(base_url('/besoins'));
    }

    public function delete(string $id): void
    {
        $this->app->db()->runQuery("DELETE FROM besoin WHERE id = ?", [(int) $id]);
        flash('success', 'Besoin supprimé.');
        $this->app->redirect(base_url('/besoins'));
    }
}
