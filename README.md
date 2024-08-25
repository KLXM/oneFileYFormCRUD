# oneFileYFormCRUD
Frontend CRUD für REDAXO YForm Tabellen (Uikit)


### Beispiel mit YCOM

```php
// Beispiel für die Nutzung der Klasse
$renderer = new YFormDataListRenderer();
$renderer->setTableName('rex_newstable');
$renderer->setFields(['city', 'name', 'status']);
$renderer->setEditLinkPattern(rex_getUrl('', '', ['func' => 'edit', 'id' => '{id}']));
$renderer->setDefaultSortField('name');
$renderer->setDefaultSortOrder('ASC');
$renderer->setTranslations([
    'status' => [
        '1' => '<span style="color: green; font-weight: bold;">Online</span>',
        '0' => '<span style="color: red;">Offline</span>',
    ]
]);
$renderer->setNewStatus(0);
$renderer->setEditStatus(2);
$renderer->setUserField('suser');

// Optional: Ändere das Template, falls nötig
// $renderer->setFormYtemplate('uikit3b');

if (rex::isFrontend() && rex_ycom_auth::getUser() !== null) {
    $rubrik= rex_ycom_auth::getUser()->getValue('rubrik');
    $renderer->setDistrictId($rubrik);
    echo $renderer->render();
}
?>
```

## Benötigtes JS

```js
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
        // Countdown direkt aus dem HTML-Element initialisieren
        var countdown = parseInt(countdownElement.textContent, 10);
        var countdownInterval = setInterval(function() {
            countdown--;
            countdownElement.textContent = countdown;
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                // Weiterleitung zur gleichen Seite ohne URL-Parameter
                window.location.href = window.location.pathname;
            }
        }, 1000);
    }
});
</script>
```



