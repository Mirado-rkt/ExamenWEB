<?php

namespace app\controllers;

use flight\Engine;

class RegionController
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    public function index(): void
    {
        $db = $this->app->db();
        $regions = $db->fetchAll("
            SELECT r.*, COUNT(v.id) AS nb_villes
            FROM region r
            LEFT JOIN ville v ON v.region_id = r.id
            GROUP BY r.id
            ORDER BY r.nom
        ");

        $this->app->render('regions/index', [
            'page_title'  => 'Régions',
            'active_menu' => 'regions',
            'regions'     => $regions,
        ]);
    }

    public function create(): void
    {
        $this->app->render('regions/form', [
            'page_title'  => 'Nouvelle région',
            'active_menu' => 'regions',
            'region'      => null,
        ]);
    }

    public function store(): void
    {
        $nom = trim($this->app->request()->data->nom ?? '');
        if ($nom === '') {
            flash('error', 'Le nom de la région est requis.');
            $this->app->redirect('/regions/create');
            return;
        }

        $this->app->db()->runQuery("INSERT INTO region (nom) VALUES (?)", [$nom]);
        flash('success', 'Région ajoutée avec succès.');
        $this->app->redirect('/regions');
    }

    public function edit(string $id): void
    {
        $region = $this->app->db()->fetchRow("SELECT * FROM region WHERE id = ?", [(int) $id]);
        if (!$region) {
            $this->app->halt(404, 'Région introuvable');
            return;
        }

        $this->app->render('regions/form', [
            'page_title'  => 'Modifier la région',
            'active_menu' => 'regions',
            'region'      => $region,
        ]);
    }

    public function update(string $id): void
    {
        $nom = trim($this->app->request()->data->nom ?? '');
        if ($nom === '') {
            flash('error', 'Le nom de la région est requis.');
            $this->app->redirect('/regions/edit/' . $id);
            return;
        }

        $this->app->db()->runQuery("UPDATE region SET nom = ? WHERE id = ?", [$nom, (int) $id]);
        flash('success', 'Région modifiée avec succès.');
        $this->app->redirect('/regions');
    }

    public function delete(string $id): void
    {
        $this->app->db()->runQuery("DELETE FROM region WHERE id = ?", [(int) $id]);
        flash('success', 'Région supprimée.');
        $this->app->redirect('/regions');
    }
}
