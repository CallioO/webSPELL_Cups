<?php

$date = '';

try {

    $_language->readModule('cups_home');

    if ($getSite == 'cup') {

        $cup_id = getParentIdByValue('id', true);

        if ($cup_id < 1) {
            throw new \Exception('unknown_cup');
        }

        if (!isset($cupArray) || !validate_array($cupArray, true)) {
            $cupArray = getcup($cup_id, 'all');
        }

        if (!isset($cupArray['phase'])) {
            throw new \Exception('unknown_cup_phase');
        }

        if (!isset($cupArray['checkin'])) {
            throw new \Exception('unknown_cup_date_checkin');
        }

        if (!isset($cupArray['start'])) {
            throw new \Exception('unknown_cup_date_start');
        }

        if (preg_match('/register/', $cupArray['phase'])) {
            $date = date('Y/m/d H:i:s', $cupArray['checkin']);
        } else if (preg_match('/checkin/', $cupArray['phase'])) {
            $date = date('Y/m/d H:i:s', $cupArray['start']);
        }

    } else if (empty($getSite) || ($getSite == 'home')) {

        $timeNow = time();

        $whereClauseArray = array();
        $whereClauseArray[] = '`start_date` >= ' . $timeNow;

        if (!iscupadmin($userID)) {
            $whereClauseArray[] = '`admin_visible` = 0';
        }

        $whereClause = implode(' AND ', $whereClauseArray);

        $selectQuery = mysqli_query(
            $_database,
            "SELECT
                    `cupID`,
                    `checkin_date`,
                    `start_date`
                FROM `" . PREFIX . "cups`
                WHERE " . $whereClause . "
                ORDER BY `start_date` ASC
                LIMIT 0, 1"
        );

        if (!$selectQuery) {
            throw new \Exception('query_select_failed');
        }

        if (mysqli_num_rows($selectQuery) > 0) {

            $ds = mysqli_fetch_array($selectQuery);

            if ($timeNow <= $ds['checkin_date']) {
                $date = date('Y/m/d H:i:s', $ds['checkin_date']);
            } else {
                $date = date('Y/m/d H:i:s', $ds['start_date']);
            }

        }

    }

    if (!isset($date)) {
        throw new \Exception('no_date_value');
    }

    if (empty($date)) {
        throw new \Exception('invalid_date_value');
    }

    $data_array = array();
    $data_array['$date'] = $date;
    $footer = $GLOBALS["_template_cup"]->replaceTemplate("footer", $data_array);
    echo $footer;

} catch (Exception $e) {}
