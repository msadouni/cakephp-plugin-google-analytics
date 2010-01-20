<h2>Accounts</h2>
<ul>
<?php foreach ($accounts as $account): ?>
    <li>
        <?php
        echo $html->link($account['Account']['title'], array(
            'action' => 'show', $account['Account']['tableId']));
        ?>
    </li>
<?php endforeach ?>
</ul>