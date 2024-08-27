<?php
class YFormDataListRenderer
{
    private string $tableName;
    private array $fields = [];
    private string $editLinkPattern;
    private string $defaultSortField = 'id';
    private string $defaultSortOrder = 'ASC';
    private array $whereConditions = [];
    private array $translations = [];
    private ?int $newStatus = 2;
    private ?int $editStatus = 1;
    private ?string $userField = null;
    private string $formYtemplate = 'uikit3,project,bootstrap';
    private array $formatCallbacks = [];
    private array $fieldLabels = [];
    private ?string $identField = null;
    private $identValue = null;

    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
        $this->loadFieldLabels();
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

    public function addWhereCondition(string $field, string $operator, $value): void
    {
        $this->whereConditions[] = [$field, $operator, $value];
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

    public function setFormatCallback(string $field, callable $callback): void
    {
        $this->formatCallbacks[$field] = $callback;
    }

    public function setIdentId(string $field, $value): void
    {
        $this->identField = $field;
        $this->identValue = $value;
    }

    private function loadFieldLabels(): void
    {
        $table = rex_yform_manager_table::get($this->tableName);
        if ($table) {
            foreach ($table->getFields() as $field) {
                $this->fieldLabels[$field->getName()] = $field->getLabel();
            }
        }
    }

    private function getFieldLabel(string $field): string
    {
        return $this->fieldLabels[$field] ?? $field;
    }

    public function render(): string
    {
        if (empty($this->tableName) || empty($this->fields) || !in_array(strtoupper($this->defaultSortOrder), ['ASC', 'DESC'])) {
            return 'Ungültige Parameter übergeben.';
        }

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
                                <script>
                                var countdown = 5;
                                var interval = setInterval(function() {
                                    countdown--;
                                    document.getElementById("countdown").textContent = countdown;
                                    if (countdown <= 0) {
                                        clearInterval(interval);
                                        window.location.href = "' . rex_getUrl(rex_article::getCurrentId()) . '";
                                    }
                                }, 1000);
                                </script>';
                    } else {
                        return '<div class="uk-alert-danger" uk-alert>Fehler: Datensatz konnte nicht gelöscht werden.</div>';
                    }
                } else {
                    return '<div class="uk-alert-danger" uk-alert>Fehler: Datensatz konnte nicht gefunden werden.</div>';
                }
            }
        }

        // Bearbeitungs- und Hinzufügen-Aktionen
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
                $yform = $dataset->getForm();
                $yform->setObjectparams('form_action', rex_getUrl(rex_article::getCurrentId(), '', ['func' => $action, 'id' => $editId]));
                $yform->setObjectparams('form_showformafterupdate', 0);
                $yform->setObjectparams('main_id', $editId);
                $yform->setObjectparams('getdata', !$isNew);
                $yform->setObjectparams('form_ytemplate', $this->formYtemplate);

                $title = $isNew ? 'Neuer Eintrag' : 'Datensatz aktualisieren';

                if ($isNew && $this->newStatus !== null) {
                    $yform->setValueField('hidden', ['status', $this->newStatus]);
                } elseif (!$isNew && $this->editStatus !== null) {
                    $yform->setValueField('hidden', ['status', $this->editStatus]);
                }

                if ($this->userField !== null && rex_ycom_auth::getUser() !== null) {
                    $username = rex_ycom_auth::getUser()->getValue('login');
                    $yform->setValueField('hidden', [$this->userField, $username]);
                }

                if ($this->identField !== null) {
                    $yform->setValueField('hidden', [$this->identField, $this->identValue]);
                }

                $form = '<div class="uk-background-muted uk-padding ">' . $dataset->executeForm($yform) . '</div>';

                if ($yform->objparams['actions_executed']) {
                    return '
                        <div class="uk-alert-success" uk-alert>
                            <h3>Der Datensatz wurde erfolgreich ' . ($isNew ? 'erstellt' : 'aktualisiert') . '.</h3>
                            <p>Sie werden in <span id="countdown">5</span> Sekunden zur Liste zurückgeleitet.</p>
                            <p><a href="' . rex_getUrl(rex_article::getCurrentId()) . '">Klicken Sie hier</a>, um sofort zur Liste zurückzukehren.</p>
                            <script>
                            var countdown = 5;
                            var interval = setInterval(function() {
                                countdown--;
                                document.getElementById("countdown").textContent = countdown;
                                if (countdown <= 0) {
                                    clearInterval(interval);
                                    window.location.href = "' . rex_getUrl(rex_article::getCurrentId()) . '";
                                }
                            }, 1000);
                            </script>';
                } else {
                    return '<h2>' . $title . '</h2>' . $form;
                }
            } else {
                return '<div class="uk-alert-danger" uk-alert>Fehler: Datensatz konnte nicht gefunden werden.</div>';
            }
        }

        // Datenanzeige
        $query = rex_yform_manager_table::get($this->tableName)->query();

        foreach ($this->whereConditions as $condition) {
            [$field, $operator, $value] = $condition;
            $query->whereRaw("`$field` $operator ?", [$value]);
        }

        $query->orderBy($currentSortField, $currentSortOrder);
        $datasets = $query->find();

        $output = '
            <div class="uk-margin-bottom">
                <a href="' . rex_getUrl(rex_article::getCurrentId(), '', ['func' => 'add']) . '" class="uk-button uk-button-primary">Neuen Eintrag erstellen</a>
                <a href="' . rex_getUrl(rex_article::getCurrentId()) . '" class="uk-button uk-button-default">Standard-Sortierung wiederherstellen</a>
            </div>
            <input class="uk-input uk-margin-bottom" id="live-search" type="text" placeholder="Nach Einträgen suchen...">
            <div class="uk-overflow-auto">
                <table class="uk-table uk-table-striped uk-table-hover">
                    <thead><tr>';

        foreach ($this->fields as $field) {
            $label = $this->getFieldLabel($field);
            $sortIcon = '';
            if ($field == $currentSortField) {
                $sortIcon = $currentSortOrder === 'ASC' ? ' &uarr;' : ' &darr;';
            }
            $output .= '<th><a href="' . rex_getUrl(rex_article::getCurrentId(), '', ['sort' => $field, 'order' => $this->defaultSortOrder === 'ASC' ? 'DESC' : 'ASC']) . '">' . htmlspecialchars($label) . $sortIcon . '</a></th>';
        }
        $output .= '<th>Aktionen</th>';
        $output .= '</tr></thead>';
        $output .= '<tbody id="data-table">';

        foreach ($datasets as $dataset) {
            $output .= '<tr>';
            foreach ($this->fields as $field) {
                $value = $dataset->getValue($field);

                if (isset($this->translations[$field]) && isset($this->translations[$field][$value])) {
                    $value = $this->translations[$field][$value];
                }

                if (isset($this->formatCallbacks[$field])) {
                    $value = call_user_func($this->formatCallbacks[$field], $value);
                }

                $output .= '<td>' . $value . '</td>';
            }

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

        $output .= '<script src="path/to/your/javascript/file.js"></script>';

        return $output;
    }
}
?>
