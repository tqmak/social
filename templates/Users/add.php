<?php

/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <?= $this->Html->link(__('Login'), ['action' => 'login'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="users form content">
            <?= $this->Form->create($user, ['enctype' => 'multipart/form-data']) ?>
            <fieldset>
                <legend><?= __('Registration') ?></legend>
                <?php
                echo $this->Form->control('image', ['type' => 'file', 'required' => 'false', 'div' => 'false']);
                echo $this->Form->control('name', ['required' => 'false']);
                echo $this->Form->control('phone', ['required' => 'false']);
                echo $this->Form->control('email', ['required' => 'false']);
                echo $this->Form->control('password', ['required' => 'false']);
                echo $this->Form->control('gender', [
                    'type' => 'radio',
                    'required' => false,
                    'options' => [
                        'Male' => 'Male',
                        'Female' => 'Female',
                        'Other' => 'Other'
                    ]
                ]);

                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>