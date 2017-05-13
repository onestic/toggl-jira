<?php
/**
 * Output CSV-formatted output from Toggl that can be entered into Jira
 */

class TogglCaller {

    public static function getPropertyValues() {
        $configurationFile = 'config.properties';
        $result = array();
        $lines = file($configurationFile);

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            $pos = strpos($line, '=');

            if ($pos === false) {
                throw new RuntimeException(sprintf('Invalid config line "%s" (no separator)', $line));
            }

            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));

            if (empty($key)) {
                throw new RuntimeException(sprintf('Invalid config line "%s" (empty key)', $line));
            }

            if (empty($value)) {
                throw new RuntimeException(sprintf('Config value "%s" cannot be empty', $key));
            }

            $result[$key] = $value;
        }

        return $result;
    }

   /**
    * TODO: Make this method longer
    *
    */
    public static function call($in) {
        $configuration = self::getPropertyValues();
        extract($configuration);

        if (empty($in)) {
            throw new InvalidArgumentException("Invalid argument value");
        }

        if (empty($JIRA_URL)) {
            throw new RuntimeException("Missing config \"JIRA_URL\"");
        }
        if (empty($JIRA_USER)) {
            throw new RuntimeException("Missing config \"JIRA_USER\"");
        }
        if (empty($JIRA_PASSWORD_FILE)) {
            throw new RuntimeException("Missing config \"JIRA_PASSWORD_FILE\"");
        }
        if (empty($TIMEZONE)) {
            throw new RuntimeException("Missing config \"TIMEZONE\"");
        }

        date_default_timezone_set($TIMEZONE);

        // Get time entries from JSON
        $entries = json_decode($in);

        if (!is_array($entries)) {
            throw new RuntimeException(sprintf('Toggl server returned error: "%s"', trim($in)));
        }
        if (count($entries) === 0) {
            throw new RuntimeException('No entries in that time period');
        }

        // Build array of data
        $days = array();
        foreach ($entries as $entry) {

            // Description should be in Jira ticket ID format: "MYPROJ-123"
            if (preg_match('/^([A-Z0-9]+-\d+)\b/', $entry->description, $matches) === 0) {
                continue;
            }

            // Currently running durations are negative
            if ($entry->duration <= 0) {
                continue;
            }

            $day = date('Y-m-d', strtotime($entry->start));

            #The day and time when the timmer sopped cointing time
            $daytime = date('Y-m-d\TH:i:s.000O',strtotime($entry->start));

            $ticket = $matches[1];

            #Time has to be rounded unlees it is a ONSYS ticket

            if (!isset($days[$day][$ticket])) {
                $days[$day][$ticket] = 0;
            }

            $days[$day][$ticket] += $entry->duration;
        }

        // Aggregate data
        $rows = array();
        foreach ($days as $day => $tickets) {
            foreach ($tickets as $ticket => $seconds) {
                $minutes = $seconds / 60;
                $hours = $minutes / 60;

                // Skip tasks less than 5 minutes
                if ($minutes < 5) {
                    continue;
                }

                #We want to log work specifying when we started it, not when it was finished

                $daytime = date('Y-m-d\TH:i:s.000O',strtotime($entry->start) - $minutes * 60);
                if ( explode('-', $ticket)[0] != "ONSYS" )
                {
                    # Troll in!
                    if ( PHP_MAJOR_VERSION < 7 ){
                        printf("##########################################################\n\n");
                        printf("              SLOW DOWN BRAVE DEVELOPER  !!!              \n\n");
                        printf("              This script uses 'intdiv' function\n");
                        printf("              which works only in PHP 7 or higher\n\n");
                        printf("              Please, update your PHP version\n\n");
                        printf("              ......or feel free to make a pool request\n");
                        printf("              .......................................................................................... .........\n");
                        printf("              .......................................................................................... .........\n");
                        printf("              .................................mmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmm............. .........\n");
                        printf("              .........................mmmmmmmmmmmmmmmm..................................mmmmm.......... .........\n");
                        printf("              ...................mmmmmmmmmmm..........mmmmm...............mm................mmm......... .........\n");
                        printf("              .................mmmm............................................mm.............mmm....... .........\n");
                        printf("              ..............mmmm....m..mmmmmm............mmm..............mm......m............mmm...... .........\n");
                        printf("              ............mmmm.................................................mm....m..........mmm..... .........\n");
                        printf("              ...........mmm............mmmmm....................m..........mmm...m....m.........mmm.... .........\n");
                        printf("              ..........mm...........m.........................m................mm...m....m.......mmm... .........\n");
                        printf("              ..........mm.........m............m..................................m..m....m.......mm... .........\n");
                        printf("              ..........mm........m..................................................m..m...........mm.. .........\n");
                        printf("              .........mm..........................................mmmmmmmmmmmmmm...................mmm. .........\n");
                        printf("              ........mmm............mmmmmmmm...................mmmmm...mmmmmmmmmmmm.................mmm .........\n");
                        printf("              ....mmmm...........mmmmmmmmmmmmmm.............mmmm......mmmmmmmm..mmm..................mmm .......\n");
                        printf("              ...mmm...mmmmm.mmm.mmmmmmmmmmmmmmm...........mmm.....mmmmmmmmmmmmmmmmm....m....mmmmmmmm.mm mm.....\n");
                        printf("              ..mm...m..................mmmmmmmmmmmmm.......mmmmmmmmm...........mm...m.................. mmm....\n");
                        printf("              .mm..m...mm.....................mmmm...........mmmmm......mmm..............mmmmmmmmmm..... ..mm...\n");
                        printf("              .mm.m..m...mmmmmm................mm.........................mmmm.......mmmmmm......mmmm... .m.mm..\n");
                        printf("              .mmm.....mmmmmmmmmm....m.........mm...........................mmmmmmmmmmm.....mm.....mmm.. .m..mmm\n");
                        printf("              .mmm.....m.......mmmmmmmm........mm...........................................mm......mm.. .m..mmm\n");
                        printf("              .mmm..m.......mm..mmmm........mmmm.........................................mmmmm.......mm. .m...mm\n");
                        printf("              .mm....m......mm...........mmmm................mmmmmmmm................mmmmm...mmmmmm..mm. .m...mm\n");
                        printf("              .mmmm...mm..mmmm..........mmmmm....................mm..............mmmmmm.....mmm.mmm.mmm. .m..mmm\n");
                        printf("              ..mm.mm.....mmmm.......mm..mmmmm.........mmmmmmm...mm..........mmmmmmm........mm......mm.. ....mmm\n");
                        printf("              ..mmm....m..mm.mmm...m........mmm..............m.mmm......mmmmmmmm.mm.......mmmm.....mm... m..mmm.\n");
                        printf("              ...mmm.....mmmmmmmmm............mmmmmm...............mmmmmmmm......mm....mmmmmm.........mm ..mm...\n");
                        printf("              ....mmm....mm.mm.mmmmmm...........mmm...........mmmmmmmm..........mmm.mmmmmmmm.......m.... .mmm...\n");
                        printf("              .....mm....mmmm..mm..mmmmmmmm........mmmmmmmmmmmmmm..mm..........mmmmmmm..mmm..........mmm mm.....\n");
                        printf("              .....mm....mmmm..mm....mmmmmmmmmmmmmmmmmmmm..........mm......mmmmmmmm....mmm............mm m......\n");
                        printf("              .....mm....mmmm..mm...mmm.......mm.......mm..........mmm.mmmmmmmmm.mm...mmm...........mmm. .......\n");
                        printf("              .....mm....mmmmmmmm...mm........mm.......mm.........mmmmmmmmmmm....mm..mmm...........mmm.. .......\n");
                        printf("              .....mm....mmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmm.......mmmmmm............mm... .......\n");
                        printf("              .....mm....mmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmm.mm...........mmm.............mmm... .......\n");
                        printf("              .....mm.....mmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmm.......mm.........mmm..............mmm.... .......\n");
                        printf("              .....mm.....mm.mmmmmmmmmmmmmmmmmmmmmmmmmmm.mm..........mmm.....mmmm...............mmm..... .......\n");
                        printf("              .....mm......mm.mm..mm...mm.....mmm........mm...........mm...mmmm...............mmm....... .......\n");
                        printf("              .....mm......mmmmmm.mmm...mm.....mm........mm............mmmmmm................mmm........ .......\n");
                        printf("              .....mm.......mmmm...mmm..mmm....mm........mm.........mmmmmm.......m.....m...mmm.......... .......\n");
                        printf("              .....mm.........mmmmmmmmm..mm....mmm.......mm..mmmmmmmmm........m.....mm...mmmm........... .......\n");
                        printf("              .....mm..............mmmmmmmmmmmmmmmmmmmmmmmmmmmmmmm.........mm....mm....mmmm ...............\n");
                        printf("              ....mmm........m..........................................m.....mm....mmmmm.. ..............\n");
                        printf("              ....mm..........m....................................mm......m.....mmmmm..... ..............\n");
                        printf("              ....mm............m..............................mm......mm.....mmmmm........ ..............\n");
                        printf("              ..mm.....mm........mmm...........mmmmmmmmmm......mm.......mmmmmm....... ............\n");
                        printf("              .mm.........m.........................mmm............mmmmm...... ........\n");
                        printf("              .mmm..........mmmmmmmmmmmmmmm.....................mmmmm...... ....\n");
                        printf("              .mmm..........................................mmmmm...... ..\n");
                        printf("              ..mmm...................................mm.mmmmm.......\n");
                        printf("              ...mmmm............................mmmmmmmmmm........\n");
                        printf("              ....mmmmm...................mmmmmmmmm..............\n");
                        printf("              .......mmmmmmmmmmmmmmmmmmmmmmmm....................\n");
                        return;
                    }
                    # Troll Out!
                    #Adjust minutes to the next number multiple of 15
                    if ( $minutes % 15 ){
                        $minutes = intdiv(($minutes + 15), 15) * 15;
                    }
                }



                $spent = sprintf('%dm', $minutes);

                $rows[] = compact('ticket', 'day', 'daytime', 'spent');
            }
        }

        // Sort data
        $tickets = array();
        $days = array();
        $daystime = array();
        foreach ($rows as $key => $row) {
            $tickets[$key]  = $row['ticket'];
            $days[$key] = $row['day'];
            $daystime[$key] = $row['daytime'];
        }
        array_multisort($tickets, SORT_ASC, $days, SORT_ASC, $daystime, SORT_ASC, $rows);

        // Script
        $url = $JIRA_URL . '/rest/api/2';
        ob_start();
?>#!/bin/bash
<?php foreach ($rows as $row): ?>

<?php
$entry = new StdClass();
$entry->started = $row['daytime'];
$entry->timeSpent = $row['spent'];
$entry->author = new StdClass;
$entry->author->self = $url . '/user?username=' . $JIRA_USER;
?>
# <?php echo $row['ticket']; ?> <?php echo date('n/j/Y', strtotime($row['day'].' 12:01 AM')); ?> <?php echo $entry->timeSpent; ?>

curl -u <?php echo $JIRA_USER ?>:$(cat <?php echo $JIRA_PASSWORD_FILE ?>) -X POST -H "Content-Type: application/json" \
--data '<?php echo json_encode($entry); ?>' \
<?php echo $url; ?>/issue/<?php echo $row['ticket']; ?>/worklog
<?php endforeach; ?>
<?php
        return ob_get_clean() . "\n";
    }
}

// Get JSON from stdin
$fd = fopen('php://stdin', 'r');
$in = '';
while (!feof($fd)) {
    $in .= fread($fd, 1024);
}
fclose($fd);

try {
    $out = TogglCaller::call($in);

    // Write string to stdout
    $fd = fopen('php://stdout', 'w');
    fwrite($fd, $out);
} catch (Exception $e) {
    print $e->getMessage();
}

