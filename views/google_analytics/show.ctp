<p><?php echo $this->Html->link('« Back to accounts', array('action' => 'index')) ?></p>
<h2><?php echo $account['Account']['name'] ?></h2>
<h3>Request info</h3>
<ul>
    <li>Total results : <?php echo $account['Account']['totalResults'] ?></li>
    <li>Start index : <?php echo $account['Account']['startIndex'] ?></li>
    <li>Items per page : <?php echo $account['Account']['itemsPerPage'] ?></li>
    <li>Start date : <?php echo $account['Account']['startDate'] ?></li>
    <li>End date : <?php echo $account['Account']['endDate'] ?></li>
</ul>
<h3>Data</h3>
<table>
    <thead>
        <tr>
        <?php if (!empty($dimensionsArray)): ?>
            <?php foreach ($dimensionsArray as $d): ?>
                <th><?php echo $d ?></th>
            <?php endforeach ?>
        <?php endif ?>
        <?php if (!empty($metricsArray)): ?>
            <?php foreach ($metricsArray as $m): ?>
                <th><?php echo $m ?></th>
            <?php endforeach ?>
        <?php endif ?>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($account['Account']['dataPoints'] as $dp): ?>
        <tr>
        <?php if (!empty($dp['dimensions'])): ?>
            <?php foreach ($dp['dimensions'] as $key => $val): ?>
                <?php if (is_numeric($key)): ?>
                    <td><?php echo $val['value'] ?></td>
                <?php elseif ($key == 'value'): ?>
                    <td><?php echo $val ?></td>
                <?php endif ?>
            <?php endforeach ?>
        <?php endif ?>
        <?php if (!empty($dp['metrics'])): ?>
            <?php foreach ($dp['metrics'] as $key => $val): ?>
                <?php if (is_numeric($key)): ?>
                    <td><?php echo $val['value'] ?></td>
                <?php elseif ($key == 'value'): ?>
                    <td><?php echo $val ?></td>
                <?php endif ?>
            <?php endforeach ?>
        <?php endif ?>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>
<h3>Search</h3>
<form action="<?php echo $html->url(array('action'=>'search')) ?>" method="post">
    <input type="hidden" name="tableId" value="<?php echo $tableId ?>">
    <div>
        <label for="start-date">Start date (YYY-MM-DD)</label>
        <input id="start-date" name="start-date" value="<?php echo $start_date ?>">
    </div>
    <div>
        <label for="end-date">End date (YYY-MM-DD)</label>
        <input id="end-date" name="end-date" value="<?php echo $end_date ?>">
    </div>
    <div>
        <label for="dimensions">Dimensions (ex : country, or country,city)</label>
        <input id="dimensions" name="dimensions" value="<?php echo $dimensions ?>">
    </div>
    <div>
        <label for="metrics">Metrics (ex : newVisits, or newVisits,uniquePageviews)</label>
        <input id="metrics" name="metrics" value="<?php echo $metrics ?>">
    </div>
    <div>
        <label for="sort">Sort (ex : newVisits, or uniquePageviews,-country)</label>
        <input id="sort" name="sort" value="<?php echo $sort ?>">
    </div>
    <input type="submit" value="Search">
</form>
