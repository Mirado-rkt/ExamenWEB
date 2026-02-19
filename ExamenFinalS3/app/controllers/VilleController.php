<?php

namespace app\controllers;

use flight\Engine;

class VilleController
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    public function index(): void
    {
        $db = $this->app->db();
        $villes = $db->fetchAll("
            SELECT v.*, r.nom AS region_nom
            FROM ville v
            JOIN region r ON v.region_id = r.id
            ORDER BY r.nom, v.nom
        ");

        $this->app->render('villes/index', [
            'page_title'  => 'Villes',
            'active_menu' => 'villes',
            'villes'      => $villes,
        ]);
    }

    public function create(): void
    {
        $regions = $this->app->db()->fetchAll("SELECT * FROM region ORDER BY nom");

        $this->app->render('villes/form', [
            'page_title'  => 'Nouvelle ville',
            'active_menu' => 'villes',
            'ville'       => null,
            'regions'     => $regions,
        ]);
    }

    public function store(): void
    {
        $nom       = trim($this->app->request()->data->nom ?? '');
        $region_id = (int) ($this->app->request()->data->region_id ?? 0);

        if ($nom === '' || $region_id === 0) {
            flash('error', 'Tous les champs sont requis.');
            $this->app->redirect('/villes/create');
            return;
        }

        $this->app->db()->runQuery(
            "INSERT INTO ville (nom, region_id) VALUES (?, ?)",
            [$nom, $region_id]
        );
        flash('success', 'Ville ajoutée avec succès.');
        $this->app->redirect('/villes');
    }

    public function edit(string $id): void
    {
        $db    = $this->app->db();
        $ville = $db->fetchRow("SELECT * FROM ville WHERE id = ?", [(int) $id]);
        if (!$ville) {
            $this->app->halt(404, 'Ville introuvable');
            return;
        }

        $regions = $db->fetchAll("SELECT * FROM region ORDER BY nom");

        $this->app->render('villes/form', [
            'page_title'  => 'Modifier la ville',
            'active_menu' => 'villes',
            'ville'       => $ville,
            'regions'     => $regions,
        ]);
    }

    public function update(string $id): void
    {
        $nom       = trim($this->app->request()->data->nom ?? '');
        $region_id = (int) ($this->app->request()->data->region_id ?? 0);

        if ($nom === '' || $region_id === 0) {
            flash('error', 'Tous les champs sont requis.');
            $this->app->redirect('/villes/edit/' . $id);
            return;
        }

        $this->app->db()->runQuery(
            "UPDATE ville SET nom = ?, region_id = ? WHERE id = ?",
            [$nom, $region_id, (int) $id]
        );
        flash('success', 'Ville modifiée avec succès.');
        $this->app->redirect('/villes');
    }

    public function delete(string $id): void
    {
        $this->app->db()->runQuery("DELETE FROM ville WHERE id = ?", [(int) $id]);
        flash('success', 'Ville supprimée.');
        $this->app->redirect('/villes');
    }
}
