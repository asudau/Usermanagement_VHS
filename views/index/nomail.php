<html>

<body>
    <div>
<h1>Informationsmail konnte nicht zugestellt werden (<?= count($data) ?>)</h1>
<p> Dies kann verschiedene Ursachen haben wie z.B. eine nicht (mehr) gültige Mailadresse. Da in diesem Fall eine Nutzer nich tüber die bevorstehende Löschung 
    des Accounts informiert werden kann, sollte im Einzalfall manuell entschieden werden wie zu verfahren ist:
    <ul>
        <li>Nutzer trotzdem löschen</li>
        <li>E-Mailadresse ändern</li>
    </ul>
</p>

<table class='default'>
    <thead>
		<tr>
        <th style='width:10%'><span>Username (Vorname Nachname)</span></th>
        <th style='width:10%'><span>EMail</span></th>
        <th style='width:5%'><span>Zuletzt aktiv am</span></th>
        <th style='width:10%'><span>Löschung geplant am</span></th>
        <th style='width:10%'>Aktionen</th>
        <!--<th>Courseware besucht?</th>-->
    </tr>
    </thead>
<?php foreach ($data as $d){ ?>
    <tr>
        
            <td><?= $d['user']['username'] . ' (' . $d['user']['Vorname'] . ' ' . $d['user']['Nachname'] . ') '?></td>
            <td><a title='Mailadresse ändern' href="<?= $this->controller->url_for('index/changemail/' . $d['user']['user_id']) ?>" data-dialog='size=small'><?= $d['user']['Email']?>  <?=Icon::create('edit', 'clickable')?></a></td>
            <td><?= $d['last_lifesign'] ? date('d.m.Y', $d['last_lifesign']) : 'noch nie' ?></td>
            <td><?= date('d.m.Y', UserConfig::get($d['user']['user_id'])->getValue(EXPIRATION_DATE)) ?></td>
            <td><a href='<?=$this->controller->url_for('index/delete_without_mail/' . $d['user']['user_id']) ?>' title='Nutzer/in trotzdem löschen'><?=Icon::create('remove-circle')?></a><br/></td>
        </tr>
<?php } ?>
</table>
</div>

</body>





