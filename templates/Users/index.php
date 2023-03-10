<?php

/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\User> $users
 */
?>
<div class="users index content">
    <?= $this->Html->link(__('Logout'), ['action' => 'logout'], ['class' => 'button float-right']) ?>
    <h3 class="ani">Welcome:<?= $user->name; ?></h3>


    <div class="table-responsive">

        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('image') ?></th>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('phone_number') ?></th>
                    <th><?= $this->Paginator->sort('email') ?></th>
                    <th><?= $this->Paginator->sort('gender') ?></th>
                    <th><?= $this->Paginator->sort('created_at') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) : ?>
                    <tr>
                        <td><?= $this->Number->format($user->id) ?></td>
                        <td><?= $this->Html->image(h($user->image), array('width' => '60px')) ?></td>
                        <td><?= h($user->name) ?></td>
                        <td><?= h($user->phone) ?></td>
                        <td><?= h($user->email) ?></td>
                        <td><?= h($user->gender) ?></td>
                        <td><?= h($user->created_at) ?></td>
                        <td class="actions">
                            <?= $this->Html->link(__(''), ['action' => 'view', $user->id], ['class' => 'fa-solid fa-eye']) ?>
                            <?= $this->Html->link(__(''), ['action' => 'edit', $user->id], ['class' => 'fa-solid fa-pen-to-square']) ?>
                            <?= $this->Form->postLink(__(''), ['action' => 'delete', $user->id], ['class' => 'fa-solid fa-trash', 'confirm' => __('Are you sure you want to delete # {0}?', $user->id)]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>