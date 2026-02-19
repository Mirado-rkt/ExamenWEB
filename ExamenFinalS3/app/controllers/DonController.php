<?php

namespace app\controllers;

use app\logic\DispatchLogic;
use flight\Engine;

class DonController
{
    protected Engine $app;

    public function __construct(Engine $app)
    {
        $this->app = $app;
    }

    public function index(): void
    {
        $db   = $this->app->db();
        $dons = $db->fetchAll("
            SELECT d.*,
                COALESCE(details.nb_lignes, 0) AS nb_lignes,
                COALESCE(details.valeur_totale, 0) AS valeur_totale
            FROM don d
            LEFT JOIN (
                SELECT dd.don_id,
                    COUNT(*) AS nb_lignes,
                    SUM(dd.quantite * tb.prix_unitaire) AS valeur_totale
                FROM don_detail dd
                JOIN type_besoin tb ON dd.type_besoin_id = tb.id
                GROUP BY dd.don_id
            ) details ON details.don_id = d.id
            ORDER BY d.date_don DESC
        ");

        $this->app->render('dons/index', [
            'page_title'  => 'Dons reçus',
            'active_menu' => 'dons',
            'dons'        => $dons,
        ]);
    }

    public function create(): void
    {
        $types = $this->app->db()->fetchAll("SELECT * FROM type_besoin ORDER BY categorie, nom");

        $this->app->render('dons/form', [
            'page_title'  => 'Nouveau don',
            'active_menu' => 'dons',
            'don'         => null,
            'types'       => $types,
        ]);
    }

    public function store(): void
    {
        $data       = $this->app->request()->data;
        $donateur   = trim($data->donateur ?? '') ?: 'Anonyme';
        $description = trim($data->description ?? '');
        $db         = $this->app->db();

        // Insérer le don
        $db->runQuery(
            "INSERT INTO don (donateur, description) VALUES (?, ?)",
            [$donateur, $description]
        );
        $don_id = (int) $db->fetchField("SELECT LAST_INSERT_ID()");

        // Insérer les détails
        $type_ids  = $data->type_besoin_id ?? [];
        $quantites = $data->detail_quantite ?? [];

        if (is_array($type_ids)) {
            for ($i = 0; $i < count($type_ids); $i++) {
                $tid = (int) ($type_ids[$i] ?? 0);
                $qty = (float) ($quantites[$i] ?? 0);
                if ($tid > 0 && $qty > 0) {
                    $db->runQuery(
                        "INSERT INTO don_detail (don_id, type_besoin_id, quantite) VALUES (?, ?, ?)",
                        [$don_id, $tid, $qty]
                    );
                }
            }
        }

        // Auto-dispatch après ajout d'un don
        DispatchLogic::executer($db);

        flash('success', 'Don enregistré avec succès.');
        $this->app->redirect(base_url('/dons'));
    }

    public function show(string $id): void
    {
        $db  = $this->app->db();
        $don = $db->fetchRow("SELECT * FROM don WHERE id = ?", [(int) $id]);
        if (!$don) {
            $this->app->halt(404, 'Don introuvable');
            return;
        }

        $details = $db->fetchAll("
            SELECT dd.*, tb.nom AS type_nom, tb.categorie, tb.prix_unitaire,
                   (dd.quantite * tb.prix_unitaire) AS valeur
            FROM don_detail dd
            JOIN type_besoin tb ON dd.type_besoin_id = tb.id
            WHERE dd.don_id = ?
            ORDER BY tb.categorie, tb.nom
        ", [(int) $id]);

        $this->app->render('dons/show', [
            'page_title'  => 'Détail du don #' . $id,
            'active_menu' => 'dons',
            'don'         => $don,
            'details'     => $details,
        ]);
    }

    public function delete(string $id): void
    {
        $this->app->db()->runQuery("DELETE FROM don WHERE id = ?", [(int) $id]);
        flash('success', 'Don supprimé.');
        $this->app->redirect(base_url('/dons'));
    }
}
