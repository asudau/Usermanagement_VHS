<html>

<body>
    <div>
<h1>Dozentenaccounts konnten nicht gel�scht werden (<?= count($data) ?>)</h1>
<p>Dozent/Innen die in einer oder mehreren Veranstaltungen als <b> einzige/r </b> Dozent/in eingetragen sind k�nnen nicht gel�scht werden. M�gliche L�sungen:
<ul>
    <li>Veranstaltung archivieren (Achtung kein Zugriff/Wiederherstellung der Veranstaltung m�glich)</li>
    <li>weitere aktive Dozierende eintragen</li>
    <li>ignorieren (Account wird nicht gel�scht werden)</li>
</ul>
    </p>

<table class='default'>
    <thead>
		<tr>
        <th style='width:10%'><span>Username (Vorname Nachname)</span></th>
        <th style='width:5%'><span>EMail</span></th>
        <th style='width:10%'><span>Liste der Veranstaltungen</span></th>
        <th style='width:10%'>Aktionen</th>
        <!--<th>Courseware besucht?</th>-->
    </tr>
    </thead>
<?php foreach ($data as $d){ ?>
    <tr>
        
            <td><?= $d['user']['username'] . ' (' . $d['user']['Vorname'] . ' ' . $d['user']['Nachname'] . ') '?></td>
            <td><?= $d['user']['Email']?></td>
            <td><?= in_array($d['status'], array("1", "2")) ? 'x' : '-' ?> </td>
            <td><a href='<?=$this->controller->url_for('index/unset/' . $d['user']['user_id']) ?>' title='Dauerhafte Ausnahme f�r Nutzer einrichten'><?=Icon::create('remove')?></a><br/></td>
        </tr>
<?php } ?>
</table>
</div>

</body>





