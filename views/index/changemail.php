<form name="change_mail" action="<?=$this->controller->url_for('index/savemail/' . $user_id)?>" method="post" style="margin-left: auto; margin-right: auto;">
     
    <div>
        <label>
            <h4><span style='color:black'><?= _("eMail-Adresse") ?>:</span></h4>
            <input value="<?= $mail ?>" type="text" name="email">
        </label>
    </div>

    <div style="text-align: center;" data-dialog-button>
        <?= \Studip\Button::create(_('Speichern'), 'submit') ?>
    </div>

</form>

