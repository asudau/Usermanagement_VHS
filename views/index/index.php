<html>

<body>
    <div>
<h1>Zur Löschung vorgemerkte Nutzeraccounts (<?= count($data) ?>)</h1>


<table class='default'>
    <thead>
		<tr>
        <th style='width:10%'><span>Username 2 (Vorname Nachname)</span></th>
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



<div style='margin-top:60px'>
<h1>Nutzeraccounts die von Löschung ausgenommen wurden (<?= count($data_spared) ?>)</h1>


<table class='default'>
    <thead>
		<tr>
        <th style='width:10%'><span>Username (Vorname Nachname)</span></th>
        <th style='width:10%'><span>EMail</span></th>
        <th style='width:10%'><span>Ausnahme hinzugefügt am</span></th>
        <th style='width:10%'>Aktionen</th>
        <!--<th>Courseware besucht?</th>-->
    </tr>
    </thead>
<?php foreach ($data_spared as $d){ ?>
    <tr>   
            <td><?= $d['user']['username'] . ' (' . $d['user']['Vorname'] . ' ' . $d['user']['Nachname'] . ') '?></td>
            <td><?= $d['user']['Email']?></td>
            <td><?= date('d.m.Y', UsermanagementAccountStatus::chdate($d['user']['user_id'])) ?></td>
            <td><a href='<?=$this->controller->url_for('index/set/' . $d['user']['user_id']) ?>' title='Dauerhafte Ausnahme für Nutzer entfernen'><?=Icon::create('remove')?></a><br/></td>
        </tr>
<?php } ?>
</table>
</div>
</body>





