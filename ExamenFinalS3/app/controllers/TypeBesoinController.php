<?php

namespace app\controllers;

use flight\Engine;

class TypeBesoinController
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    public function index(): void
    {
        $types = $this->app->db()->fetchAll("SELECT * FROM type_besoin ORDER BY categorie, nom");

        $this->app->render('types/index', [
            'page_title'  => 'Types de besoin',
            'active_menu' => 'types',
            'types'       => $types,
        ]);
    }

    public function create(): void
    {
        $this->app->render('types/form', [
            'page_title'  => 'Nouveau type de besoin',
            'active_menu' => 'types',
            'type'        => null,
        ]);
    }

    public function store(): void
    {
        $data = $this->app->request()->data;
        $nom          = trim($data->nom ?? '');
        $categorie    = trim($data->categorie ?? '');
        $prix_unitaire = (float) ($data->prix_unitaire ?? 0);

        if ($nom === '' || $categorie === '' || $prix_unitaire <= 0) {
            flash('error', 'Tous les champs sont requis et le prix doit être positif.');
            $this->app->redirect('/types-besoin/create');
            return;
        }

        $this->app->db()->runQuery(
            "INSERT INTO type_besoin (nom, categorie, prix_unitaire) VALUES (?, ?, ?)",
            [$nom, $categorie, $prix_unitaire]
        );
        flash('success', 'Type de besoin ajouté avec succès.');
        $this->app->redirect('/types-besoin');
    }

    public function edit(string $id): void
    {
        $type = $this->app->db()->fetchRow("SELECT * FROM type_besoin WHERE id = ?", [(int) $id]);
        if (!$type) {
            $this->app->halt(404, 'Type introuvable');
            return;
        }

        $this->app->render('types/form', [
            'page_title'  => 'Modifier le type de besoin',
            'active_menu' => 'types',
            'type'        => $type,
        ]);
    }

    public function update(string $id): void
    {
        $data = $this->app->request()->data;
        $nom          = trim($data->nom ?? '');
        $categorie    = trim($data->categorie ?? '');
        $prix_unitaire = (float) ($data->prix_unitaire ?? 0);

        if ($nom === '' || $categorie === '' || $prix_unitaire <= 0) {
            flash('error', 'Tous les champs sont requis et le prix doit être positif.');
            $this->app->redirect('/types-besoin/edit/' . $id);
            return;
        }

        $this->app->db()->runQuery(
            "UPDATE type_besoin SET nom = ?, categorie = ?, prix_unitaire = ? WHERE id = ?",
            [$nom, $categorie, $prix_unitaire, (int) $id]
        );
        flash('success', 'Type de besoin modifié avec succès.');
        $this->app->redirect('/types-besoin');
    }

    public function delete(string $id): void
    {
        $this->app->db()->runQuery("DELETE FROM type_besoin WHERE id = ?", [(int) $id]);
        flash('success', 'Type de besoin supprimé.');
        $this->app->redirect('/types-besoin');
    }
}
