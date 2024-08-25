# oneFileYFormCRUD
Frontend CRUD für REDAXO YForm Tabellen (Uikit)

YFormDataListRenderer Class

## Überblick

Die `YFormDataListRenderer`-Klasse ist ein flexibles Tool, um beliebige Datensätze aus Tabellen anzuzeigen und zu verwalten, die mit YForm in Redaxo verwaltet werden. Sie ist vielseitig einsetzbar, ob für Blog-Artikel, News-Einträge, Produkte oder andere Arten von Daten. In diesem Beispiel zeigen wir, wie man die Klasse für die Verwaltung von Blog-Artikeln nutzen kann.

## Wie's funktioniert

### 1. Renderer erstellen

Zuerst wird eine Instanz der `YFormDataListRenderer`-Klasse benötigt:

```php
$renderer = new YFormDataListRenderer();
```

### 2. Tabelle festlegen

Hier wird die Tabelle angegeben, aus der die Blog-Artikel abgerufen werden sollen. In unserem Beispiel nehmen wir an, dass die Tabelle `rex_blog_articles` heißt:

```php
$renderer->setTableName('rex_blog_articles');
```

### 3. Felder definieren

Nun werden die Felder festgelegt, die in der Liste angezeigt werden sollen. Für Blog-Artikel könnten das z. B. `title` (Titel des Artikels), `date` (Veröffentlichungsdatum), `status` (Online/Offline-Status) und `author` (Autor des Artikels) sein:

```php
$renderer->setFields(['title', 'date', 'status', 'author']);
```

### 4. Bearbeitungslink-Muster festlegen

Definiere das URL-Muster für das Bearbeiten von Artikeln. `{id}` ist dabei der Platzhalter für die Artikel-ID:

```php
$renderer->setEditLinkPattern(rex_getUrl('', '', ['func' => 'edit', 'id' => '{id}']));
```

### 5. Standard-Sortieroptionen festlegen

Wähle das Standardfeld und die Reihenfolge für die Sortierung aus. Für Blog-Artikel könnte es sinnvoll sein, nach dem Veröffentlichungsdatum in absteigender Reihenfolge zu sortieren:

```php
$renderer->setDefaultSortField('date');
$renderer->setDefaultSortOrder('DESC');
```

### 6. Übersetzungen einrichten (Optional)

Für das `status`-Feld können Übersetzungen oder benutzerdefiniertes HTML festgelegt werden. So kann beispielsweise der Status `1` als "Online" und `0` als "Offline" angezeigt werden:

```php
$renderer->setTranslations([
    'status' => [
        '1' => '<span style="color: green; font-weight: bold;">Online</span>',
        '0' => '<span style="color: red;">Offline</span>',
    ]
]);
```

### 7. Standardstatus für neue und bearbeitete Artikel (Optional)

Wenn beim Erstellen eines neuen Artikels oder beim Bearbeiten eines bestehenden Artikels der Status automatisch gesetzt werden soll:

```php
$renderer->setNewStatus(0);  // Neuer Artikel ist standardmäßig "Offline"
$renderer->setEditStatus(1); // Bearbeiteter Artikel wird auf "Online" gesetzt
```

### 8. Benutzerfeld festlegen (Optional)

Falls der aktuelle Benutzer als Autor gespeichert werden soll, einfach das entsprechende Feld angeben:

```php
$renderer->setUserField('author');
```

### 9. Formulartemplate anpassen (Optional)

Wenn ein eigenes YForm-Template verwendet werden soll, kann dies so festgelegt werden:

```php
$renderer->setFormYtemplate('custom_template');
```

### 10. Liste rendern

Zum Schluss wird die Liste mit der `render()`-Methode gerendert:

```php
if (rex::isFrontend() && rex_ycom_auth::getUser() !== null) {
    echo $renderer->render();
}
```

### JavaScript einbinden

Damit alles rund läuft, sollte das zugehörige JavaScript geladen werden. Das betrifft z. B. die Live-Suche und den Countdown für die automatische Rückleitung nach dem Löschen oder Bearbeiten eines Artikels:

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

## Vollständiger Beispielcode 
Javascript siehe oben

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

// Übersetzungen einrichten
$renderer->setTranslations([
    'status' => [
        '1' => '<span style="color: green; font-weight: bold;">Online</span>',
        '0' => '<span style="color: red;">Offline</span>',
    ]
]);

// Standardstatus für neue und bearbeitete Artikel festlegen
$renderer->setNewStatus(0);  // Neuer Artikel ist standardmäßig "Offline"
$renderer->setEditStatus(1); // Bearbeiteter Artikel wird auf "Online" gesetzt

// Benutzerfeld festlegen (falls der aktuelle Benutzer als Autor gespeichert werden soll)
$renderer->setUserField('author');

// Optional: Formulartemplate anpassen
// $renderer->setFormYtemplate('custom_template');

// Liste rendern und ausgeben
if (rex::isFrontend() && rex_ycom_auth::getUser() !== null) {
    echo $renderer->render();
}

?>
```



