<html>

<body>
    <div>
<h1>Informationsmail konnte nicht zugestellt werden (<?= count($data) ?>)</h1>
<p> Dies kann verschiedene Ursachen haben wie z.B. eine nicht (mehr) g�ltige Mailadresse. Da in diesem Fall eine Nutzer nich t�ber die bevorstehende L�schung 
    des Accounts informiert werden kann, sollte im Einzalfall manuell entschieden werden wie zu verfahren ist:
    <ul>
        <li>Nutzer trotzdem l�schen</li>
        <li>E-Mailadresse �ndern</li>
    </ul>
</p>

<table class='default'>
    <thead>
		<tr>
        <th style='width:10%'><span>Username (Vorname Nachname)</span></th>
        <th style='width:10%'><span>EMail</span></th>
        <th style='width:10%'><span>L�schung geplant am</span></th>
        <th style='width:10%'>Aktionen</th>
        <!--<th>Courseware besucht?</th>-->
    </tr>
    </thead>
<?php foreach ($data as $d){ ?>
    <tr>
        
            <td><?= $d['user']['username'] . ' (' . $d['user']['Vorname'] . ' ' . $d['user']['Nachname'] . ') '?></td>
            <td><?= $d['user']['Email']?></td>
            <td><?= date('d.m.Y', UserConfig::get($d['user']['user_id'])->getValue(EXPIRATION_DATE)) ?></td>
            <td><a href='<?=$this->controller->url_for('index/delete_without_mail/' . $d['user']['user_id']) ?>' title='Nutzer/in trotzdem l�schen'><?=Icon::create('remove-circle')?></a><br/></td>
        </tr>
<?php } ?>
</table>
</div>

</body>





