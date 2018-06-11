<html>

<fieldset>
<h1>Konfiguration</h1>

<? foreach($settings as $setting): ?>
<form method="post" action="<?=$controller->url_for('/settings/save/' . $setting)?>">
    <p>
        <label><?= Config::get()->getMetadata($setting)['description'] ?>:</label><br>
        <input style="width:1000px" value="<?= Config::get()->getValue($setting)?>" type="text" name="value">
        <button type="submit" name="submit" > Speichern </button>
    </p>
    <hr>
</form>
<? endforeach ?>



</fieldset>





