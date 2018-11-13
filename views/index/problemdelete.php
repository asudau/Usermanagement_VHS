<html>

<body>
    <div>
<h1>Dozentenaccounts konnten nicht gelöscht werden (<?= count($data) ?>)</h1>
<p>Dozent/Innen die in einer oder mehreren Veranstaltungen als <b> einzige/r </b> Dozent/in eingetragen sind können nicht gelöscht werden. Mögliche Lösungen:
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
                <?= $membership->course_name ? $membership->course_name . '<br>' : ''?> 
            </td>
            <td>
                <a href='<?=$this->controller->url_for('index/archiveseminar/' . $membership->seminar_id) ?>' title='Veranstaltung archivieren'><?=Icon::create('archive3')?></a>
                <?= $this->controller->get_mp($membership->seminar_id) ?>
            </td>
            
        </tr>
    <? endforeach ?>
<?php } ?>
</table>
</div>

</body>





