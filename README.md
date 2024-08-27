# oneFileYFormCRUD

Frontend-CRUD für REDAXO YForm Tabellen (Uikit)

## YFormDataListRenderer Class

### Installation

Die `YFormDataListRenderer`-Class in den Lib-Ordner des Projekt-AddOns kopieren und bei Bedarf gestalterisch anpassen.

### Überblick

Die `YFormDataListRenderer`-Class ist ein flexibles Tool, um Datensätze aus Tabellen, die mit YForm in REDAXO verwaltet werden, anzuzeigen und zu verwalten. Diese Class eignet sich für eine Vielzahl von Anwendungsfällen, wie z. B. Blog-Artikel, News-Einträge, Produkte oder andere Datentypen. Im Folgenden wird gezeigt, wie die Class für die Verwaltung von Blog-Artikeln verwendet werden kann.

### Wie es funktioniert

#### 1. Renderer erstellen

Erstellen Sie eine Instanz der `YFormDataListRenderer`-Class:

```php
$renderer = new YFormDataListRenderer();
```

#### 2. Tabelle festlegen

Legen Sie die Tabelle fest, aus der die Datensätze abgerufen werden sollen. Zum Beispiel:

```php
$renderer->setTableName('rex_blog_articles');
```

#### 3. Felder definieren

Bestimmen Sie die Felder, die in der Liste angezeigt werden sollen:

```php
$renderer->setFields(['title', 'date', 'status', 'author']);
```

#### 4. Bearbeitungslink-Muster festlegen

Definieren Sie das URL-Muster für den Bearbeitungslink. `{id}` ist dabei der Platzhalter für die Datensatz-ID:

```php
$renderer->setEditLinkPattern(rex_getUrl('', '', ['func' => 'edit', 'id' => '{id}']));
```

#### 5. Standard-Sortieroptionen festlegen

Legen Sie das Standardfeld und die Standardreihenfolge für die Sortierung fest:

```php
$renderer->setDefaultSortField('date');
$renderer->setDefaultSortOrder('DESC');
```

#### 6. Bedingungen für die Datenauswahl hinzufügen

Fügen Sie Bedingungen für die Auswahl der Datensätze hinzu. Zum Beispiel:

```php
$renderer->addWhereCondition('status', '=', 1);
```

#### 7. Übersetzungen einrichten (optional)

Definieren Sie Übersetzungen für bestimmte Feldwerte:

```php
$renderer->setTranslations([
    'status' => [
        '1' => '<span style="color: green; font-weight: bold;">Online</span>',
        '0' => '<span style="color: red;">Offline</span>',
    ]
]);
```

#### 8. Standardstatus für neue und bearbeitete Datensätze (optional)

Legen Sie fest, welcher Status beim Erstellen oder Bearbeiten von Datensätzen gesetzt werden soll:

```php
$renderer->setNewStatus(0);  // Neuer Datensatz ist standardmäßig "Offline"
$renderer->setEditStatus(1); // Bearbeiteter Datensatz wird auf "Online" gesetzt
```

#### 9. Benutzerfeld festlegen (optional)

Speichern Sie den aktuellen Benutzer als Autor:

```php
$renderer->setUserField('author');
```

#### 10. Formulartemplate anpassen (optional)

Legen Sie ein benutzerdefiniertes YForm-Template fest:

```php
$renderer->setFormYtemplate('custom_template');
```

#### 11. Formatierungs-Callbacks hinzufügen

Fügen Sie Formatierungs-Callbacks für bestimmte Felder hinzu:

```php
$renderer->setFormatCallback('title', function($value) {
    return '<strong>' . htmlspecialchars($value) . '</strong>';
});

$renderer->setFormatCallback('date', function($value) {
    return date('d.m.Y', strtotime($value));
});
```

#### 12. Identifikationsfeld festlegen (optional)

Falls nötig, können Sie ein Identifikationsfeld und dessen Wert setzen:

```php
$renderer->setIdentId('user_id', rex_ycom_auth::getUser()->getId());
```

#### 13. Liste rendern

Zum Schluss rendern Sie die Liste und geben sie aus:

```php
if (rex::isFrontend() && rex_ycom_auth::getUser() !== null) {
    echo $renderer->render();
}
```

### Vollständiges Beispiel

Hier ein vollständiger Beispielcode, der alle oben genannten Methoden verwendet:

```php
<?php

$renderer = new YFormDataListRenderer();

// Tabelle festlegen
$renderer->setTableName('rex_blog_articles');

// Felder definieren
$renderer->setFields(['title', 'date', 'status', 'author']);

// Bearbeitungslink-Muster festlegen
$renderer->setEditLinkPattern(rex_getUrl('', '', ['func' => 'edit', 'id' => '{id}']));

// Standard-Sortieroptionen festlegen
$renderer->setDefaultSortField('date');
$renderer->setDefaultSortOrder('DESC');

// Bedingungen für die Datenauswahl hinzufügen
$renderer->addWhereCondition('status', '=', 1);

// Übersetzungen einrichten
$renderer->setTranslations([
    'status' => [
        '1' => '<span style="color: green; font-weight: bold;">Online</span>',
        '0' => '<span style="color: red;">Offline</span>',
    ]
]);

// Standardstatus für neue und bearbeitete Datensätze festlegen
$renderer->setNewStatus(0);
$renderer->setEditStatus(1);

// Benutzerfeld festlegen (optional)
$renderer->setUserField('author');

// Optional: Formulartemplate anpassen
// $renderer->setFormYtemplate('custom_template');

// Callbacks für die Formatierung
$renderer->setFormatCallback('title', function($value) {
    return '<strong>' . htmlspecialchars($value) . '</strong>';
});

$renderer->setFormatCallback('date', function($value) {
    return date('d.m.Y', strtotime($value));
});

// Identifikationsfeld festlegen (optional)
$renderer->setIdentId('user_id', rex_ycom_auth::getUser()->getId());

// Liste rendern und ausgeben
if (rex::isFrontend() && rex_ycom_auth::getUser() !== null) {
    echo $renderer->render();
}

?>
```

### JavaScript einbinden

Um die Benutzerfreundlichkeit zu verbessern, können Sie optional JavaScript für Funktionen wie die Live-Suche oder einen Countdown-Timer hinzufügen.

```javascript
document.addEventListener("DOMContentLoaded", function() {
    var liveSearchElement = document.getElementById("live-search");
    if (liveSearchElement) {
        liveSearchElement.addEventListener("keyup", function() {
            var searchValue = this.value.toLowerCase();
            var tableRows = document.getElementById("data-table").getElementsByTagName("tr");

            for (var i = 0; i < tableRows.length; i++) {
                var rowText = tableRows[i].textContent.toLowerCase();
                if (rowText.indexOf(searchValue) > -1) {
                    tableRows[i].style.display = "";
                } else {
                    tableRows[i].style.display = "none";
                }
            }
        });
    }

    var countdownElement = document.getElementById("countdown");
    if (countdownElement) {
        var countdown = parseInt(countdownElement.textContent, 10);
        var countdownInterval = setInterval(function() {
            countdown--;
            countdownElement.textContent = countdown;
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                window.location.href = window.location.pathname;
            }
        }, 1000);
    }
});
```
