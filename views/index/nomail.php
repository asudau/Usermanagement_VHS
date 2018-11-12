<html>

<body>
    <div>
<h1>Informationsmail konnte nicht zugestellt werden (<?= count($data) ?>)</h1>


<table class='default'>
    <thead>
		<tr>
        <th style='width:10%'><span>Username (Vorname Nachname)</span></th>
        <th style='width:10%'><span>EMail</span></th>
        <th style='width:5%'><span>1. Infomail erhalten</span></th>
        <th style='width:5%'><span>2. Infomail erhalten</span></th>
        <th style='width:10%'><span>Löschung geplant am</span></th>
        <th style='width:10%'>Aktionen</th>
        <!--<th>Courseware besucht?</th>-->
    </tr>
    </thead>
<?php foreach ($data as $d){ ?>
    <tr>
        
            <td><?= $d['user']['username'] . ' (' . $d['user']['Vorname'] . ' ' . $d['user']['Nachname'] . ') '?></td>
            <td><?= $d['user']['Email']?></td>
            <td><?= in_array($d['status'], array("1", "2")) ? 'x' : '-' ?> </td>
            <td><?= $d['status']== 2 ? 'x' : '-' ?> </td>
            <td><?= date('d.m.Y', UserConfig::get($d['user']['user_id'])->getValue(EXPIRATION_DATE)) ?></td>
            <td><a href='<?=$this->controller->url_for('index/unset/' . $d['user']['user_id']) ?>' title='Dauerhafte Ausnahme für Nutzer einrichten'><?=Icon::create('remove')?></a><br/></td>
        </tr>
<?php } ?>
</table>
</div>

</body>





