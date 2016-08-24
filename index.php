<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title>Kims Freshe Ordbok</title>
    <link href='css/bootstrap.min.css' rel='stylesheet'>
    <link href='main.css' rel='stylesheet'>
    <script>
        function makeUncertain(li) {
            console.log("kaller makeUncertain!");
            var a = li.getElementsByTagName('a')[0];
            var word = a.innerHTML;
            $.ajax({
                type: "POST",
                url: "makeUncertain.php",
                data: "word=" + word,
                success: function (data) {
                    console.log("success! :");
                    console.log(data);
                    li.className = 'disableClick';
                    if (a.className == 'answer-anchor') {
                        li.innerHTML = "<div class='answer text-warning'><i class='glyphicon glyphicon-warning-sign'></i><h3> " + word + " er gjort usikker</h3></div>";
                    } else {
                        li.innerHTML = "<a class='text-warning'>(" + word + ")</a>";
                    }
                }
            });
        }
        function makeValid(li) {
            console.log("kaller makeValid!");
            var a = li.getElementsByTagName('a')[0];
            var word = a.innerHTML;
            $.ajax({
                type: "POST",
                url: "makeValid.php",
                data: "word=" + word,
                success: function (data) {
                    console.log("success! :");
                    console.log(data);
                    li.className = 'disableClick';
                    if (a.className == 'answer-anchor') {
                        li.innerHTML = "<div class='answer text-success'><i class='glyphicon glyphicon-ok-sign'></i><h3> " + word + " har blitt godkjent!</h3></div>";
                    } else {
                        li.innerHTML = "<a class='text-success'>" + word + "!</a>";
                    }
                }
            });
        }
        function editValid(a) {
            //fjern forrige hvis fler
            if (a.parentNode.className != 'selectedLI') {
                var previous = document.getElementsByClassName('selectedLI')[0];
                if (typeof previous != 'undefined') {
                    previous.className = '';
                    var restoreA = previous.firstElementChild;
                    previous.innerHTML = restoreA.outerHTML;
                    console.log("previous");
                    console.log(previous);
                }
                console.log(a);
                console.log(a.parentNode);
                console.log(previous);
                a.parentNode.className = 'selectedLI';
                a.parentNode.innerHTML = a.parentNode.innerHTML +
                    "<button type='button' onclick='makeUncertain(this.parentNode)' class='btn btn-sm btn-warning'>Usikker</button>" +
                    "<button type='button' onclick='deleteWord(this.parentNode)' class='btn btn-sm btn-danger'>Slett</button>";
            }
        }
        function editUncertain(a) {
            //fjern forrige hvis fler
            if (a.parentNode.className != 'selectedLI') {
                var previous = document.getElementsByClassName('selectedLI')[0];
                if (typeof previous != 'undefined') {
                    previous.className = '';
                    var restoreA = previous.firstElementChild;
                    previous.innerHTML = restoreA.outerHTML;
                    console.log("previous");
                    console.log(previous);
                }
                console.log(a);
                console.log(a.parentNode);
                console.log(previous);
                a.parentNode.className = 'selectedLI';
                a.parentNode.innerHTML = a.parentNode.innerHTML +
                    "<button type='button' onClick='makeValid(this.parentNode)' class='btn btn-sm btn-success'>Godkjenn</button>" +
                    "<button type='button' onClick='deleteWord(this.parentNode)' class='btn btn-sm btn-danger'>Slett</button>";
            }
        }
        function showAddButton(a) {
            if (a.parentNode.innerHTML.substring(a.parentNode.innerHTML.length - 9) !== '</button>') {
                a.parentNode.innerHTML = a.parentNode.innerHTML +
                    "<button type='button' onClick='addWord(this.parentNode)' class='btn btn-sm btn-success'>Legg til</button>";
            }
        }
        function showButtonsWhenValid(a) {
            if (a.parentNode.innerHTML.substring(a.parentNode.innerHTML.length - 9) !== '</button>') {
                a.parentNode.innerHTML = a.parentNode.innerHTML +
                    "<button type='button' onClick='makeUncertain(this.parentNode)' class='btn btn-sm btn-warning'>Usikker</button>" +
                    "<button type='button' onClick='deleteWord(this.parentNode)' class='btn btn-sm btn-danger'>Slett</button>";
            }
        }
        function showButtonsWhenUncertain(a) {
            if (a.parentNode.innerHTML.substring(a.parentNode.innerHTML.length - 9) !== '</button>') {
                a.parentNode.innerHTML = a.parentNode.innerHTML +
                    "<button type='button' onClick='makeValid(this.parentNode)' class='btn btn-sm btn-success'>Godkjenn</button>" +
                    "<button type='button' onClick='deleteWord(this.parentNode)' class='btn btn-sm btn-danger'>Slett</button>";
            }
        }
        function addWord(li) {
            console.log("kaller addWord!");
            var word = li.getElementsByTagName('a')[0].innerHTML;
            console.log(word);
            $.ajax({
                type: "POST",
                url: "addWord.php",
                data: "word=" + word,
                success: function (data) {
                    console.log("success! :");
                    console.log(data);
                    li.innerHTML = "<div class='answer text-success'><i class='glyphicon glyphicon-ok-sign'></i><h3> " + word + " ble lagt til!</h3></div>";
                }
            });
        }
        function deleteWord(li) {
            console.log("kaller deleteWord!");
            var a = li.getElementsByTagName('a')[0];
            var word = a.innerHTML;
            $.ajax({
                type: "POST",
                url: "deleteWord.php",
                data: "word=" + word,
                success: function (data) {
                    console.log("success! :");
                    console.log(data);
                    li.className = 'disableClick';
                    if (a.className == 'answer-anchor') {
                        li.innerHTML = "<div class='answer text-danger'><i class='glyphicon glyphicon-remove-sign'></i><h3> " + word + " har blitt slettet</h3></div>";
                    } else {
                        li.innerHTML = "<a class='text-danger'><strike>" + word + "</strike></a>";
                    }
                }
            });
        }
    </script>
</head>
<body>
<div class='navbar navbar-default navbar-static-top'>
    <div class='container'>
        <div class='navbar-header'>
            <a href='/index.php' class='navbar-brand' id="brand-name">Kims Freshe Ordbok</a>
        </div>
    </div>
</div>
<div class='container'>
    <div class='row well well-md'>
        <div class='col-md-12'>
            <h3>Hei <?php echo ucfirst($_SERVER['REMOTE_USER']); ?>, søk etter ord:</h3>


            <form class="search-form" action="index.php" method="POST">
                <!--<label for="search-word">Søkeord</label>-->
                <div class="input-group">
                    <input type="text" autofocus id="search-word" name="query"
                           value="<?php if (isset($_POST['query'])) {                                                                                                                         //autofocus pattern="^[a-zA-ZæøåÆØÅ]*-?[a-zA-ZæøåÆØÅ]*$" required title="Kun bokstaver og maks 1 blank(-)"
                               echo $_POST['query'];
                           } ?>" onfocus="this.setSelectionRange(0, this.value.length)"
                           onclick="this.setSelectionRange(0, this.value.length)" autocorrect="off" autocomplete="off"
                           maxlength="10">
                    <input type="submit" value="Søk" class='btn btn-lg btn-primary btn-responsive'>
                    <p class="anagram-label">Anagrammer</p>
                    <input type="checkbox" name="anagram" <?php if (isset($_POST['anagram'])) {
                        echo "checked";
                    } ?>/>
                </div>
            </form>


            <?php

            function mysort($a, $b)
            {
                return strlen($b) - strlen($a);
            }

            $query = $_POST['query'];
            $anagram = $_POST['anagram'];

            $url = parse_url(getenv("DATABASE_URL"));
            $host = $url["host"];
            $username = $url["user"];
            $password = $url["pass"];
            $database = substr($url["path"], 1);

            $yesArray = array();
            $maybeArray = array();

            function lensortIncreasing($a, $b)
            {
                $la = strlen($a);
                $lb = strlen($b);
                if ($la == $lb) {
                    return strcmp($a, $b);
                }
                return $la - $lb;
            }

            function mbStringToArray($string)
            {
                $strlen = mb_strlen($string);
                while ($strlen) {
                    $array[] = mb_substr($string, 0, 1, "UTF-8");
                    $string = mb_substr($string, 1, $strlen, "UTF-8");
                    $strlen = mb_strlen($string);
                }
                return $array;
            }

            function startsWith($haystack, $needle)
            {
                $length = strlen($needle);
                return (substr($haystack, 0, $length) === $needle);
            }

            function endsWith($haystack, $needle)
            {
                $length = strlen($needle);
                if ($length == 0) {
                    return true;
                }

                return (substr($haystack, -$length) === $needle);
            }

            $vowels = array("Æ", "Ø", "Å");
            $replacements = array("{", "}", "|");

            $blanks = 0;
            for ($i = 0; $i < mb_strlen($query); $i++) {
                if ($query[$i] == "-") {
                    $blanks++;
                }
            }

            if ($blanks > 1) {
                echo "<br><h3>Søket støtter maksimalt én blank (-)</h3>";
            } else if (isset($_POST['query'])) {

                $db_conn = pg_connect("user=$username password=$password host=$host sslmode=require dbname=$database") or die('Could not connect: ' . pg_last_error());
                pg_query("SET NAMES 'utf8'");

                //sjekke alle anagrammer
                if (isset($_POST['anagram'])) {

                    //$vowels = array("Æ", "Ø", "Å");
                    //$replacements = array("{", "}", "|");


                    $split = mbStringToArray(str_replace($vowels, $replacements, mb_strtoupper($query, 'UTF-8')));
                    natcasesort($split);
                    $stringWithBlank = implode($split);
                    $list = array();


                    //hvis inneholder blank
                    if ($stringWithBlank[0] == "-") {
                        $scrabbleAlphabet = "ABCDEFGHIJKLMNOPQRSTUÜVWXYZ{}|";
                        for ($k = 0; $k < mb_strlen($scrabbleAlphabet); $k++) {
                            $split = mbStringToArray(str_replace("-", $scrabbleAlphabet[$k], $stringWithBlank));

                            //finne alle kombinasjoner av strengen, med en blank
                            $a = $split;
                            $len = count($a);
                            for ($i = 1; $i < (1 << $len); $i++) {
                                $c = '';
                                for ($j = 0; $j < $len; $j++)
                                    if ($i & (1 << $j))
                                        $c .= $a[$j];
                                $split = str_split($c);
                                natcasesort($split);
                                $c = implode($split);
                                //kun samme lengde ord:  and strlen($c) == strlen($query)
                                if (strlen($c) > 1 and !in_array($c, $list)) {
                                    $list[] = $c;
                                }
                            }
                        }

                    } else {
                        //finne alle kombinasjoner av strengen
                        $a = $split;
                        $len = count($a);
                        for ($i = 1; $i < (1 << $len); $i++) {
                            $c = '';
                            for ($j = 0; $j < $len; $j++)
                                if ($i & (1 << $j))
                                    $c .= $a[$j];
                            $split = str_split($c);
                            natcasesort($split);
                            $c = implode($split);
                            //kun samme lengde ord:  and strlen($c) == strlen($query)
                            if (strlen($c) > 1 and !in_array($c, $list)) {
                                $list[] = $c;
                            }
                        }
                    }

                    function lensort($a, $b)
                    {
                        $la = strlen($a);
                        $lb = strlen($b);
                        if ($la == $lb) {
                            return strcmp($a, $b);
                        }
                        return $lb - $la;
                    }

                    foreach ($list as $index => $entry) {
                        $list[$index] = str_replace($replacements, $vowels, $entry);
                    }


                    $sql = "SELECT * FROM dictionary WHERE alpha IN ('" . implode("','", $list) . "')";

                    $result = pg_exec($db_conn, $sql) or die('Query failed: ' . pg_last_error());
                    if (pg_numrows($result) !== 0) {
                        while ($row = pg_fetch_array($result)) {
                            if ($row[isValid] === 't') {
                                $yesArray[] = str_replace($vowels, $replacements, $row[word]);
                            } else {
                                $maybeArray[] = str_replace($vowels, $replacements, $row[word]);
                            }
                        }
                    }
                    $str = mb_strtoupper($query, 'UTF-8');
                    //spesialtilfellet ÅA
                    if ((strpos($str, 'Å') !== FALSE && strpos($str, 'A') !== FALSE) ||
                        (strpos($str, '-') !== FALSE && (strpos($str, 'Å') !== FALSE || strpos($str, 'A') !== FALSE))
                    ) {
                        $yesArray[] = "ÅA";
                    }
                    if (count($yesArray) === 0 && count($maybeArray) === 0) {
                        echo "<br><h3>$query gir ingen anagrammer...</h3>";
                    }

                    usort($yesArray, 'lensort');
                    usort($maybeArray, 'lensort');

                } else { //hvis ikke anagram
                    if ($query === "åa") {
                        echo "<br><div class='answer text-success'><i class='glyphicon glyphicon-ok-sign'></i><a class='answer-anchor'>$query</a><h3> ble funnet, HURRA!!!</h3></div>";
                    } else {

                        $startsWithStar = startsWith($query, "*");
                        $endsWithStar = endsWith($query, "*");
                        // hvis søket starter/slutter med stjerne
                        if ($startsWithStar || $endsWithStar) {
                            $validQuery = true;
                            for ($i = 1; $i < mb_strlen($query) - 1; $i++) {
                                if ($query[$i] === "*") {
                                    $validQuery = false;
                                }
                            }
                            if ($validQuery) {
                                $likeQuery = str_replace("*", "%", mb_strtoupper($query, 'UTF-8'));
                                $queryNeedle = str_replace("*", "", mb_strtoupper($query, 'UTF-8'));
                                $sql = "SELECT * FROM dictionary WHERE word like '$likeQuery'";
                                $result = pg_exec($db_conn, $sql) or die('Query failed: ' . pg_last_error());
                                if (pg_numrows($result) !== 0) {
                                    while ($row = pg_fetch_array($result)) {
                                        if ($row[isValid] === 't') {
                                            if ($startsWithStar && endsWith($row[word], $queryNeedle) ||
                                                $endsWithStar && startsWith($row[word], $queryNeedle) ||
                                                ($startsWithStar && $endsWithStar && stristr($row[word], $queryNeedle))
                                            ) {
                                                $yesArray[] = str_replace($vowels, $replacements, $row[word]);
                                            }

                                        } else {
                                            if ($startsWithStar && endsWith($row[word], $queryNeedle) ||
                                                $endsWithStar && startsWith($row[word], $queryNeedle) ||
                                                ($startsWithStar && $endsWithStar && stristr($row[word], $queryNeedle))
                                            ) {
                                                $maybeArray[] = str_replace($vowels, $replacements, $row[word]);
                                            }
                                        }
                                    }
                                    usort($yesArray, 'lensortIncreasing');
                                    usort($maybeArray, 'lensortIncreasing');
                                } else {
                                    echo "<br><h3>søket gir ingen treff...</h3>";
                                }
                            } else {
                                echo "invalid query (* in middle of word)";
                            }
                        } else { //sjekke et enkelt ord
                            $upperQuery = mb_strtoupper($query, 'UTF-8');
                            $sql = "SELECT * FROM dictionary WHERE word = '$upperQuery'";
                            $result = pg_exec($db_conn, $sql) or die('Query failed: ' . pg_last_error());
                            if (pg_numrows($result) === 0) {
                                echo "<br><div class='answer text-danger'><i class='glyphicon glyphicon-remove-sign'></i><a class='answer-anchor' onclick='showAddButton(this)'>$query</a><h3> er ikke i listen..</h3></div>";
                            } else {
                                $row = pg_fetch_array($result);
                                if ($row[isValid] === 't') {
                                    echo "<br><div class='answer text-success'><i class='glyphicon glyphicon-ok-sign'></i><a class='answer-anchor' onclick='showButtonsWhenValid(this)'>$query</a><h3> ble funnet, HURRA!!!</h3></div>";
                                } else {
                                    echo "<br><div class='answer text-warning'><i class='glyphicon glyphicon-warning-sign'></i><a class='answer-anchor' onclick='showButtonsWhenUncertain(this)'>$query</a><h3> er kanskje et ord...</h3></div>";
                                }
                            }
                        }
                    }
                }


            }
            ?>

            <!--<button type='button' class='btn btn-lg btn-primary'>Søk</button>-->
        </div>
    </div>
</div>
<div class='container'>
    <div class='row'>

        <div class='col-md-12'>
            <div class='panel-group'>
                <div class='panel panel-success <?php if (empty($yesArray)) {
                    echo "hidden";
                } ?>'>
                    <div class='panel-heading'>
                        <h4 class='panel-title'>
                            <a data-toggle='collapse' href='#systemsOperational'><i class='glyphicon glyphicon-ok-sign'
                                                                                    id="panel-i"></i>Godkjente ord</a>
                        </h4>
                    </div>
                    <div id='systemsOperational' class='panel-collapse collapse in'>
                        <div class='panel-body'>
                            <ul class='list-unstyled'>
                                <?php foreach ($yesArray as $yesWord): ?>
                                    <li>
                                        <a onclick='editValid(this)'><?= str_replace($replacements, $vowels, $yesWord); ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class='panel panel-warning <?php if (empty($maybeArray)) {
                    echo "hidden";
                } ?>'>
                    <div class='panel-heading'>
                        <h4 class='panel-title'>
                            <a data-toggle='collapse' href='#weatherAlert'><i class='glyphicon glyphicon-question-sign'
                                                                              id="panel-i"></i>Ord som kanskje er
                                godkjent</a>
                        </h4>
                    </div>
                    <div id='weatherAlert' class='panel-collapse collapse in'>
                        <div class='panel-body'>
                            <ul class='list-unstyled'>
                                <?php foreach ($maybeArray as $maybeWord): ?>
                                    <li>
                                        <a onclick='editUncertain(this)'><?= str_replace($replacements, $vowels, $maybeWord); ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src='https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js'></script>
<script src='js/bootstrap.min.js'></script>
<script>

</script>
</body>
</html>
