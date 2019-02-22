<html>

<body>
    <div>
<h1>Dozentenaccounts konnten nicht gelöscht werden (<?= count($data) ?>)</h1>
<p>Dozent/Innen die in einer oder mehreren Veranstaltungen als <b> einzige/r </b> Dozent/in eingetragen sind können nicht gelöscht werden. Mögliche Lösungen zum Beheben des Konfliktes:
<ul>
    <li>Veranstaltung archivieren (Achtung kein Zugriff/Wiederherstellung der Veranstaltung möglich)</li>
    <li>weitere aktive Dozierende eintragen</li>
    <li>ignorieren (Account wird nicht gelöscht werden)</li>
</ul>
    </p>

<table class='default'>
    <thead>
		<tr>
        <th style='width:10%'><span>Username (Vorname Nachname)</span></th>
        <th style='width:5%'><span>EMail</span></th>
        <th style='width:5%'><span>Anzahl Dozenten</span></th>
        <th style='width:10%'><span>Veranstaltung</span></th>
        <th data-sort="text" style='width:5%'><span>Zuletzt aktiv am</span></th>
        <th style='width:10%'>Aktionen</th>
        <!--<th>Courseware besucht?</th>-->
    </tr>
    </thead>
<?php foreach ($data as $d){ ?>
     <? foreach ($d['seminare'] as $membership): ?>
        <tr>
            <td><?= $d['user']['username'] . ' (' . $d['user']['Vorname'] . ' ' . $d['user']['Nachname'] . ') '?></td>
            <td><?= $d['user']['Email']?></td>
            <td><?= CourseMember::countByCourseAndStatus($membership->seminar_id, 'dozent') ?> </td>
            <td>
                <a href='<?=URLHelper::getLink("/seminar_main.php?auswahl=" . $membership->course_id )?>' target="_blank">
                    <?= $membership->course_name ? $membership->course_name . '<br>' : ''?> 
                </a>
            </td>
            <td><?= $d['last_lifesign'] ? date('d.m.Y', $d['last_lifesign']) : 'noch nie' ?></td>
            <td>
                <a onclick="return confirm('Veranstaltung archivieren? Dieser Schritt kann nciht rückgängig gemacht werden! Sie haben anschließend keinen Zugriff mehr auf Inhalte der Veranstaltung.')" href='<?=$this->controller->url_for('index/archiveseminar/' . $membership->seminar_id) ?>' title='Veranstaltung archivieren'><?=Icon::create('archive3')?></a>
                <?= $this->controller->get_mp($membership->seminar_id) ?>
            </td>
            
        </tr>
    <? endforeach ?>
<?php } ?>
</table>
</div>

</body>





