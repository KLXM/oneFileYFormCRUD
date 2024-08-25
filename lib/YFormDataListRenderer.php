<?php
class YFormDataListRenderer
{
    private string $tableName;
    private array $fields = [];
    private string $editLinkPattern;
    private string $defaultSortField = 'id';
    private string $defaultSortOrder = 'ASC';
    private ?int $districtId = null;
    private array $translations = [];
    private ?int $newStatus = null;
    private ?int $editStatus = null;
    private ?string $userField = null;
    private string $formYtemplate = 'uikit3,project,bootstrap'; // Standardwert

    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    public function setEditLinkPattern(string $editLinkPattern): void
    {
        $this->editLinkPattern = $editLinkPattern;
    }

    public function setDefaultSortField(string $defaultSortField): void
    {
        $this->defaultSortField = $defaultSortField;
    }

    public function setDefaultSortOrder(string $defaultSortOrder): void
    {
        $this->defaultSortOrder = $defaultSortOrder;
    }

    public function setDistrictId(?int $districtId): void
    {
        $this->districtId = $districtId;
    }

    public function setTranslations(array $translations): void
    {
        $this->translations = $translations;
    }

    public function setNewStatus(?int $newStatus): void
    {
        $this->newStatus = $newStatus;
    }

    public function setEditStatus(?int $editStatus): void
    {
        $this->editStatus = $editStatus;
    }

    public function setUserField(?string $userField): void
    {
        $this->userField = $userField;
    }

    public function setFormYtemplate(string $formYtemplate): void
    {
        $this->formYtemplate = $formYtemplate;
    }

    public function render(): string
    {
        // Sicherheitsprüfung und Standardwerte
        if (empty($this->tableName) || empty($this->fields) || !in_array(strtoupper($this->defaultSortOrder), ['ASC', 'DESC'])) {
            return 'Ungültige Parameter übergeben.';
        }

        // Aktuelle Sortierung abfragen oder Standardwerte verwenden
        $currentSortField = rex_request('sort', 'string', $this->defaultSortField);
        $currentSortOrder = rex_request('order', 'string', $this->defaultSortOrder);

        // Prüfen, ob eine Löschaktion ausgeführt werden soll
        if (rex_request('func', 'string') === 'delete') {
            $deleteId = rex_request('id', 'int');
            if ($deleteId) {
                $dataset = rex_yform_manager_dataset::get($deleteId, $this->tableName);
                if ($dataset) {
                    $deleteResult = $dataset->delete();
                    if ($deleteResult) {
                        return '<div class="uk-alert-success" uk-alert>Datensatz wurde erfolgreich gelöscht.</div>
                                <p>Sie werden in <span id="countdown">5</span> Sekunden zur Liste zurückgeleitet.</p>
                                <p><a href="' . rex_getUrl(rex_article::getCurrentId()) . '">Klicken Sie hier</a>, um sofort zur Liste zurückzukehren.</p>
                                <script src="path/to/your/javascript/file.js"></script>';
                    } else {
                        return '<div class="uk-alert-danger" uk-alert>Fehler: Datensatz konnte nicht gelöscht werden.</div>';
                    }
                } else {
                    return '<div class="uk-alert-danger" uk-alert>Fehler: Datensatz konnte nicht gefunden werden.</div>';
                }
            }
        }

        // Prüfen, ob eine Bearbeitungsaktion oder eine Neuerstellung ausgeführt werden soll
        $action = rex_request('func', 'string');
        $editId = rex_request('id', 'int', -1); // -1 bedeutet neuer Datensatz
        $isNew = ($editId === -1);

        if ($action === 'edit' || $action === 'add') {
            if (!$isNew) {
                $dataset = rex_yform_manager_dataset::get($editId, $this->tableName);
            } else {
                $dataset = rex_yform_manager_dataset::create($this->tableName);
            }

            if ($dataset) {
                // Erstelle das YForm-Objekt für das Formular
                $yform = $dataset->getForm();
                $yform->setObjectparams('form_action', rex_getUrl(rex_article::getCurrentId(), '', ['func' => $action, 'id' => $editId]));
                $yform->setObjectparams('form_showformafterupdate', 0); // Formular nach Update nicht erneut anzeigen
                $yform->setObjectparams('main_id', $editId);
                $yform->setObjectparams('getdata', !$isNew); // Nur Daten laden, wenn es sich um ein Edit handelt
                $yform->setObjectparams('form_ytemplate', $this->formYtemplate); // Verwende das Template aus der Setter-Methode

                // Überschrift je nach Aktion festlegen
                $title = $isNew ? 'Neuer Eintrag' : 'Datensatz aktualisieren';

                // Status für neuen Eintrag oder bearbeiteten Eintrag festlegen
                if ($isNew && $this->newStatus !== null) {
                    $yform->setValueField('hidden', ['status', $this->newStatus]);
                } elseif (!$isNew && $this->editStatus !== null) {
                    $yform->setValueField('hidden', ['status', $this->editStatus]);
                }

                // YCOM-Nutzernamen ermitteln und speichern, wenn ein Feldname angegeben wurde
                if ($this->userField !== null && rex_ycom_auth::getUser() !== null) {
                    $username = rex_ycom_auth::getUser()->getValue('login');
                    $yform->setValueField('hidden', [$this->userField, $username]);
                }

                // District ID setzen
                $yform->setValueField('hidden', ['district_id', $this->districtId]);

                // Formular ausführen
                $form = '<div class="uk-background-muted uk-padding ">' . $dataset->executeForm($yform) . '</div>';

                // Wenn das Formular erfolgreich ausgeführt wurde
                if ($yform->objparams['actions_executed']) {
                    return '
                        <div class="uk-alert-success" uk-alert>
                            <h3>Der Datensatz wurde erfolgreich ' . ($isNew ? 'erstellt' : 'aktualisiert') . '.</h3>
                            <p>Sie werden in <span id="countdown">5</span> Sekunden zur Liste zurückgeleitet.</p>
                            <p><a href="' . rex_getUrl(rex_article::getCurrentId()) . '">Klicken Sie hier</a>, um sofort zur Liste zurückzukehren.</p>
                        </div>
                        <script src="path/to/your/javascript/file.js"></script>';
                } else {
                    // Falls das Formular nicht erfolgreich gespeichert wurde, zeige es erneut an
                    return '<h2>' . $title . '</h2>' . $form;
                }
            } else {
                return '<div class="uk-alert-danger" uk-alert>Fehler: Datensatz konnte nicht gefunden werden.</div>';
            }
        }

        // Sortierreihenfolge umkehren
        $nextSortOrder = ($currentSortOrder === 'ASC') ? 'DESC' : 'ASC';

        // Filterbedingungen für district_id aufbauen
        $whereSql = '';
        if (!is_null($this->districtId)) {
            $whereSql = 'district_id = ' . intval($this->districtId);
        }

        // Datenbankabfrage für die Datensätze mit YForm
        $query = rex_yform_manager_table::get($this->tableName)->query();
        if ($whereSql) {
            $query->whereRaw($whereSql);
        }
        $query->orderBy($currentSortField, $currentSortOrder);
        $datasets = $query->find();

        // HTML-Ausgabe starten
        $output = '
            <div class="uk-margin-bottom">
                <a href="' . rex_getUrl(rex_article::getCurrentId(), '', ['func' => 'add']) . '" class="uk-button uk-button-primary">Neuen Eintrag erstellen</a>
                <a href="' . rex_getUrl(rex_article::getCurrentId()) . '" class="uk-button uk-button-default">Standard-Sortierung wiederherstellen</a>
            </div>
            <input class="uk-input uk-margin-bottom" id="live-search" type="text" placeholder="Nach Einträgen suchen...">
            <div class="uk-overflow-auto">
                <table class="uk-table uk-table-striped uk-table-hover">
                    <thead><tr>';

        // Kopfzeile für die Tabelle erstellen und Sortierlinks hinzufügen
        foreach ($this->fields as $field) {
            $sortIcon = '';
            if ($field == $currentSortField) {
                $sortIcon = $currentSortOrder === 'ASC' ? ' &uarr;' : ' &darr;';
            }
            $output .= '<th><a href="' . rex_getUrl(rex_article::getCurrentId(), '', ['sort' => $field, 'order' => $nextSortOrder]) . '">' . htmlspecialchars($field) . $sortIcon . '</a></th>';
        }
        $output .= '<th>Aktionen</th>';
        $output .= '</tr></thead>';
        $output .= '<tbody id="data-table">';

        // Zeilen für jeden Datensatz erstellen
        foreach ($datasets as $dataset) {
            $output .= '<tr>';
            foreach ($this->fields as $field) {
                $value = $dataset->getValue($field);

                // Übersetzung für bestimmte Felder anwenden, z.B. 'status'
                if (isset($this->translations[$field]) && isset($this->translations[$field][$value])) {
                    $value = $this->translations[$field][$value];
                }

                $output .= '<td>' . $value . '</td>'; // Hier wird $value direkt ausgegeben, um HTML zu ermöglichen
            }

            // Bearbeiten- und Löschen-Icons erstellen
            $id = $dataset->getId();
            $editLink = rex_getUrl(rex_article::getCurrentId(), '', ['func' => 'edit', 'id' => $id]);
            $deleteLink = rex_getUrl(rex_article::getCurrentId(), '', ['func' => 'delete', 'id' => $id]);

            $output .= '<td>
                            <div class="uk-grid-small uk-child-width-auto" uk-grid>
                                <div><a href="' . $editLink . '" uk-tooltip="Bearbeiten" uk-icon="icon: pencil"></a></div>
                                <div><a href="' . $deleteLink . '" uk-tooltip="Löschen" uk-icon="icon: trash" onclick="return confirm(\'Wirklich löschen?\');"></a></div>
                            </div>
                        </td>';

            $output .= '</tr>';
        }

        $output .= '</tbody></table></div>';

        // JavaScript für den Live-Filter hinzufügen
        $output .= '<script src="path/to/your/javascript/file.js"></script>';

        return $output;
    }
}
