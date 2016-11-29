<?php
$sql = isset ($_POST['sql']) ? $_POST['sql'] : '';
?>
<html><head><title>BD</title></head>
    <body>
        <form method="post">
            <textarea name="sql"><?php echo $sql; ?></textarea>
            <br/>
            <input type="submit" value="ok"/>
        </form>
<?php
if (count($_POST)) {
    $host = '187.45.196.180';
    $user = 'lembrefacil1';
    $pass = 'Adv0607word';
    $instance = 'lembrefacil1';

    echo '<pre>';

    $link = mysql_connect($host, $user, $pass);
    if (!$link) {
        echo('Could not connect: ' . mysql_error());
    }

    mysql_select_db($instance);

    $result = mysql_query($sql);
    if (!$result) {
        $message  = 'Invalid query: ' . mysql_error() . "\n";
        $message .= 'Whole query: ' . $query;
        echo($message);
    }

    if ($row = mysql_fetch_assoc($result)) {
        print '<table border>';
        $header = '<tr>';
        foreach (array_keys($row) as $cadah) {
            $header = '<th>'.$cadah.'</th>';
        }
        $header = '</tr>';
        print $header;

        $i=0;
        do {
            if (($i++ % 10) == 0) print $header;
            print '<tr>';
            foreach (array_values($row) as $cadav) {
                print '<td>'.($cadav?$cadav:'.').'</td>';
            }
            print '</tr>';
            //var_dump($row);
        } while ($row = mysql_fetch_assoc($result)) ;
        print '</table>';

    }

    echo '</pre>';

    mysql_close($link);

}
?>
    </body>
</html>
