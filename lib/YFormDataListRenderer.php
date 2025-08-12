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
    private string $framework = 'uikit';
    private array $cssTemplates = [];
    private string $displayMode = 'table';
    private bool $showActions = true;

    public function __construct()
    {
        $this->initializeCssTemplates();
    }

    private function initializeCssTemplates(): void
    {
        $this->cssTemplates = [
            'uikit' => [
                'alert_success' => 'uk-alert-success',
                'alert_danger' => 'uk-alert-danger',
                'alert_wrapper' => 'uk-alert',
                'button_primary' => 'uk-button uk-button-primary',
                'button_default' => 'uk-button uk-button-default',
                'input' => 'uk-input',
                'margin_bottom' => 'uk-margin-bottom',
                'overflow_auto' => 'uk-overflow-auto',
                'table' => 'uk-table uk-table-striped uk-table-hover',
                'background_muted' => 'uk-background-muted',
                'padding' => 'uk-padding',
                'grid_small' => 'uk-grid-small uk-child-width-auto',
                'tooltip' => 'uk-tooltip',
                'icon' => 'uk-icon',
            ],
            'bootstrap3' => [
                'alert_success' => 'alert alert-success',
                'alert_danger' => 'alert alert-danger',
                'alert_wrapper' => '',
                'button_primary' => 'btn btn-primary',
                'button_default' => 'btn btn-default',
                'input' => 'form-control',
                'margin_bottom' => 'margin-bottom',
                'overflow_auto' => 'table-responsive',
                'table' => 'table table-striped table-hover',
                'background_muted' => 'bg-muted',
                'padding' => 'padding',
                'grid_small' => 'row',
                'tooltip' => 'title',
                'icon' => 'glyphicon',
            ],
            'bootstrap4' => [
                'alert_success' => 'alert alert-success',
                'alert_danger' => 'alert alert-danger',
                'alert_wrapper' => '',
                'button_primary' => 'btn btn-primary',
                'button_default' => 'btn btn-secondary',
                'input' => 'form-control',
                'margin_bottom' => 'mb-3',
                'overflow_auto' => 'table-responsive',
                'table' => 'table table-striped table-hover',
                'background_muted' => 'bg-light',
                'padding' => 'p-3',
                'grid_small' => 'row',
                'tooltip' => 'title',
                'icon' => 'fas',
            ],
            'bootstrap5' => [
                'alert_success' => 'alert alert-success',
                'alert_danger' => 'alert alert-danger',
                'alert_wrapper' => '',
                'button_primary' => 'btn btn-primary',
                'button_default' => 'btn btn-secondary',
                'input' => 'form-control',
                'margin_bottom' => 'mb-3',
                'overflow_auto' => 'table-responsive',
                'table' => 'table table-striped table-hover',
                'background_muted' => 'bg-light',
                'padding' => 'p-3',
                'grid_small' => 'row',
                'tooltip' => 'title',
                'icon' => 'bi',
            ],
            'custom' => [
                'alert_success' => 'success-message',
                'alert_danger' => 'error-message',
                'alert_wrapper' => 'alert',
                'button_primary' => 'btn-primary',
                'button_default' => 'btn-default',
                'input' => 'input',
                'margin_bottom' => 'mb',
                'overflow_auto' => 'overflow-auto',
                'table' => 'table striped hover',
                'background_muted' => 'bg-muted',
                'padding' => 'p',
                'grid_small' => 'grid-small',
                'tooltip' => 'tooltip',
                'icon' => 'icon',
            ]
        ];
    }

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

    public function setFramework(string $framework): void
    {
        if (isset($this->cssTemplates[$framework])) {
            $this->framework = $framework;
        }
    }

    public function setDisplayMode(string $mode): void
    {
        if (in_array($mode, ['table', 'cards', 'list'])) {
            $this->displayMode = $mode;
        }
    }

    public function setShowActions(bool $showActions): void
    {
        $this->showActions = $showActions;
    }

    public function setCssTemplate(string $framework, array $template): void
    {
        $this->cssTemplates[$framework] = $template;
    }

    public function setCssClass(string $framework, string $key, string $class): void
    {
        if (isset($this->cssTemplates[$framework])) {
            $this->cssTemplates[$framework][$key] = $class;
        }
    }

    private function getCssClass(string $key): string
    {
        return $this->cssTemplates[$this->framework][$key] ?? '';
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
                        $alertClass = $this->getCssClass('alert_success');
                        $alertWrapper = $this->getCssClass('alert_wrapper');
                        $alertWrapperAttr = $alertWrapper ? ' ' . $alertWrapper : '';
                        if ($this->framework === 'uikit') {
                            $alertWrapperAttr = ' uk-alert';
                        }
                        return '<div class="' . $alertClass . '"' . $alertWrapperAttr . '>Datensatz wurde erfolgreich gelöscht.</div>
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
                        $alertClass = $this->getCssClass('alert_danger');
                        $alertWrapper = $this->getCssClass('alert_wrapper');
                        $alertWrapperAttr = $alertWrapper ? ' ' . $alertWrapper : '';
                        if ($this->framework === 'uikit') {
                            $alertWrapperAttr = ' uk-alert';
                        }
                        return '<div class="' . $alertClass . '"' . $alertWrapperAttr . '>Fehler: Datensatz konnte nicht gelöscht werden.</div>';
                    }
                } else {
                    $alertClass = $this->getCssClass('alert_danger');
                    $alertWrapper = $this->getCssClass('alert_wrapper');
                    $alertWrapperAttr = $alertWrapper ? ' ' . $alertWrapper : '';
                    if ($this->framework === 'uikit') {
                        $alertWrapperAttr = ' uk-alert';
                    }
                    return '<div class="' . $alertClass . '"' . $alertWrapperAttr . '>Fehler: Datensatz konnte nicht gefunden werden.</div>';
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

                $form = '<div class="' . $this->getCssClass('background_muted') . ' ' . $this->getCssClass('padding') . '">' . $dataset->executeForm($yform) . '</div>';

                if ($yform->objparams['actions_executed']) {
                    $alertClass = $this->getCssClass('alert_success');
                    $alertWrapper = $this->getCssClass('alert_wrapper');
                    $alertWrapperAttr = $alertWrapper ? ' ' . $alertWrapper : '';
                    if ($this->framework === 'uikit') {
                        $alertWrapperAttr = ' uk-alert';
                    }
                    return '
                        <div class="' . $alertClass . '"' . $alertWrapperAttr . '>
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
                $alertClass = $this->getCssClass('alert_danger');
                $alertWrapper = $this->getCssClass('alert_wrapper');
                $alertWrapperAttr = $alertWrapper ? ' ' . $alertWrapper : '';
                if ($this->framework === 'uikit') {
                    $alertWrapperAttr = ' uk-alert';
                }
                return '<div class="' . $alertClass . '"' . $alertWrapperAttr . '>Fehler: Datensatz konnte nicht gefunden werden.</div>';
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

        return $this->renderDataDisplay($datasets, $currentSortField, $currentSortOrder);
    }

    private function renderDataDisplay($datasets, $currentSortField, $currentSortOrder): string
    {
        $buttons = '';
        if ($this->showActions) {
            $buttons = '
            <div class="' . $this->getCssClass('margin_bottom') . '">
                <a href="' . rex_getUrl(rex_article::getCurrentId(), '', ['func' => 'add']) . '" class="' . $this->getCssClass('button_primary') . '">Neuen Eintrag erstellen</a>
                <a href="' . rex_getUrl(rex_article::getCurrentId()) . '" class="' . $this->getCssClass('button_default') . '">Standard-Sortierung wiederherstellen</a>
            </div>';
        }

        $searchInput = '<input class="' . $this->getCssClass('input') . ' ' . $this->getCssClass('margin_bottom') . '" id="live-search" type="text" placeholder="Nach Einträgen suchen...">';

        switch ($this->displayMode) {
            case 'cards':
                return $buttons . $searchInput . $this->renderCardsView($datasets, $currentSortField, $currentSortOrder);
            case 'list':
                return $buttons . $searchInput . $this->renderListView($datasets, $currentSortField, $currentSortOrder);
            case 'table':
            default:
                return $buttons . $searchInput . $this->renderTableView($datasets, $currentSortField, $currentSortOrder);
        }
    }

    private function renderTableView($datasets, $currentSortField, $currentSortOrder): string
    {
        $output = '
            <div class="' . $this->getCssClass('overflow_auto') . '">
                <table class="' . $this->getCssClass('table') . '">
                    <thead><tr>';

        foreach ($this->fields as $field) {
            $label = $this->getFieldLabel($field);
            $sortIcon = '';
            if ($field == $currentSortField) {
                $sortIcon = $currentSortOrder === 'ASC' ? ' &uarr;' : ' &darr;';
            }
            $output .= '<th><a href="' . rex_getUrl(rex_article::getCurrentId(), '', ['sort' => $field, 'order' => $this->defaultSortOrder === 'ASC' ? 'DESC' : 'ASC']) . '">' . htmlspecialchars($label) . $sortIcon . '</a></th>';
        }
        if ($this->showActions) {
            $output .= '<th>Aktionen</th>';
        }
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

            if ($this->showActions) {
                $id = $dataset->getId();
                $editLink = rex_getUrl(rex_article::getCurrentId(), '', ['func' => 'edit', 'id' => $id]);
                $deleteLink = rex_getUrl(rex_article::getCurrentId(), '', ['func' => 'delete', 'id' => $id]);

                if ($this->framework === 'uikit') {
                    $output .= '<td>
                                    <div class="uk-grid-small uk-child-width-auto" uk-grid>
                                        <div><a href="' . $editLink . '" uk-tooltip="Bearbeiten" uk-icon="icon: pencil"></a></div>
                                        <div><a href="' . $deleteLink . '" uk-tooltip="Löschen" uk-icon="icon: trash" onclick="return confirm(\'Wirklich löschen?\');"></a></div>
                                    </div>
                                </td>';
                } else {
                    $editIcon = $this->getEditIcon();
                    $deleteIcon = $this->getDeleteIcon();
                    $output .= '<td>
                                    <div class="' . $this->getCssClass('grid_small') . '">
                                        <div><a href="' . $editLink . '" title="Bearbeiten">' . $editIcon . '</a></div>
                                        <div><a href="' . $deleteLink . '" title="Löschen" onclick="return confirm(\'Wirklich löschen?\');">' . $deleteIcon . '</a></div>
                                    </div>
                                </td>';
                }
            }

            $output .= '</tr>';
        }

        return $output . '</tbody></table></div>';
    }

    private function renderCardsView($datasets, $currentSortField, $currentSortOrder): string
    {
        $output = '<div class="' . $this->getCssClass('grid_small') . '" data-grid>';

        foreach ($datasets as $dataset) {
            $output .= '<div class="card-item">';
            if ($this->framework === 'bootstrap3' || $this->framework === 'bootstrap4' || $this->framework === 'bootstrap5') {
                $output .= '<div class="card">';
                $output .= '<div class="card-body">';
            } elseif ($this->framework === 'uikit') {
                $output .= '<div class="uk-card uk-card-default uk-card-body">';
            } else {
                $output .= '<div class="card">';
            }

            foreach ($this->fields as $field) {
                $value = $dataset->getValue($field);
                $label = $this->getFieldLabel($field);

                if (isset($this->translations[$field]) && isset($this->translations[$field][$value])) {
                    $value = $this->translations[$field][$value];
                }

                if (isset($this->formatCallbacks[$field])) {
                    $value = call_user_func($this->formatCallbacks[$field], $value);
                }

                $output .= '<p><strong>' . htmlspecialchars($label) . ':</strong> ' . $value . '</p>';
            }

            if ($this->showActions) {
                $id = $dataset->getId();
                $editLink = rex_getUrl(rex_article::getCurrentId(), '', ['func' => 'edit', 'id' => $id]);
                $deleteLink = rex_getUrl(rex_article::getCurrentId(), '', ['func' => 'delete', 'id' => $id]);

                $editIcon = $this->getEditIcon();
                $deleteIcon = $this->getDeleteIcon();

                $output .= '<div class="actions">';
                $output .= '<a href="' . $editLink . '" class="' . $this->getCssClass('button_default') . '" title="Bearbeiten">' . $editIcon . ' Bearbeiten</a> ';
                $output .= '<a href="' . $deleteLink . '" class="' . $this->getCssClass('button_default') . '" title="Löschen" onclick="return confirm(\'Wirklich löschen?\');">' . $deleteIcon . ' Löschen</a>';
                $output .= '</div>';
            }

            if ($this->framework === 'bootstrap3' || $this->framework === 'bootstrap4' || $this->framework === 'bootstrap5') {
                $output .= '</div></div>';
            } elseif ($this->framework === 'uikit') {
                $output .= '</div>';
            } else {
                $output .= '</div>';
            }
            $output .= '</div>';
        }

        return $output . '</div>';
    }

    private function renderListView($datasets, $currentSortField, $currentSortOrder): string
    {
        $output = '<ul class="data-list">';

        foreach ($datasets as $dataset) {
            $output .= '<li>';

            foreach ($this->fields as $field) {
                $value = $dataset->getValue($field);
                $label = $this->getFieldLabel($field);

                if (isset($this->translations[$field]) && isset($this->translations[$field][$value])) {
                    $value = $this->translations[$field][$value];
                }

                if (isset($this->formatCallbacks[$field])) {
                    $value = call_user_func($this->formatCallbacks[$field], $value);
                }

                $output .= '<span class="field-' . $field . '"><strong>' . htmlspecialchars($label) . ':</strong> ' . $value . '</span> ';
            }

            if ($this->showActions) {
                $id = $dataset->getId();
                $editLink = rex_getUrl(rex_article::getCurrentId(), '', ['func' => 'edit', 'id' => $id]);
                $deleteLink = rex_getUrl(rex_article::getCurrentId(), '', ['func' => 'delete', 'id' => $id]);

                $editIcon = $this->getEditIcon();
                $deleteIcon = $this->getDeleteIcon();

                $output .= '<span class="actions">';
                $output .= '<a href="' . $editLink . '" title="Bearbeiten">' . $editIcon . '</a> ';
                $output .= '<a href="' . $deleteLink . '" title="Löschen" onclick="return confirm(\'Wirklich löschen?\');">' . $deleteIcon . '</a>';
                $output .= '</span>';
            }

            $output .= '</li>';
        }

        return $output . '</ul>';
    }

    private function getEditIcon(): string
    {
        switch ($this->framework) {
            case 'bootstrap3':
                return '<span class="glyphicon glyphicon-pencil"></span>';
            case 'bootstrap4':
                return '<i class="fas fa-edit"></i>';
            case 'bootstrap5':
                return '<i class="bi bi-pencil"></i>';
            case 'uikit':
                return ''; // UIKit uses uk-icon attribute
            case 'custom':
            default:
                return '<span class="icon-edit">✏️</span>';
        }
    }

    private function getDeleteIcon(): string
    {
        switch ($this->framework) {
            case 'bootstrap3':
                return '<span class="glyphicon glyphicon-trash"></span>';
            case 'bootstrap4':
                return '<i class="fas fa-trash"></i>';
            case 'bootstrap5':
                return '<i class="bi bi-trash"></i>';
            case 'uikit':
                return ''; // UIKit uses uk-icon attribute
            case 'custom':
            default:
                return '<span class="icon-delete">🗑️</span>';
        }
    }
}
