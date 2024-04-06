<?php
    require_once('data/version.inc.php');
?>
<html lang="en-us">
<head>
    <title>Eden's Glitch</title>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"/>
    <link rel="stylesheet" href="css/glitch.css?v=<?=VERSION?>">
    <link rel="stylesheet" href="css/characters.css?v=<?=VERSION?>">
    <link rel="stylesheet" href="css/profile.css?v=<?=VERSION?>">
    <link rel="stylesheet" href="css/editor.css?v=<?=VERSION?>">
    <script src="https://cdn.jsdelivr.net/npm/showdown@2.1.0/dist/showdown.min.js"></script>
    <script type="text/javascript" src="js/utilities.js?v=<?=VERSION?>"></script>
    <script type="text/javascript" src="js/history.js?v=<?=VERSION?>"></script>
    <script type="text/javascript" src="js/component.js?v=<?=VERSION?>"></script>
    <script type="text/javascript" src="js/characters.js?v=<?=VERSION?>"></script>
    <script type="text/javascript" src="js/profile.js?v=<?=VERSION?>"></script>
    <script type="text/javascript" src="js/glitch.js?v=<?=VERSION?>"></script>
    <script type="text/javascript" src="js/editor.js?v=<?=VERSION?>"></script>
    <script type="text/javascript" src="js/character_editor.js?v=<?=VERSION?>"></script>
    <script type="text/javascript" src="js/home.js?v=<?=VERSION?>"></script>
    <script type="text/javascript">
        // For debugging
        var _glitch;
        window.onload = function() {
            let glitch = new Glitch();
            glitch.register();
            glitch.load();
            _glitch = glitch;
        };
    </script>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-V3DL87V391"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-V3DL87V391');
    </script>
</head>
<body>
    <div class="tabContainer" id="mainContainer">
        <div class="toolbar">
            <div class="tabButton active toolbarButton" data-tab="home">&#8962;</div>
            <div class="tabButtons">
                <div class="tabButton toolbarButton" data-tab="characters">Machines</div>
                <div class="tabButton toolbarButton admin" data-tab="characterEditor" style="display: none">Machine Editor</div>
            </div>
            <div id="profileButton" class="tabButton toolbarButton loggedout" data-tab="profile"></div>
        </div>

        <div class="tab" id="home" style="display: none">
            <div class="tile navigation" data-tab="characters">Machines</div>
            <div class="tile navigation admin" data-tab="characterEditor" style="display: none">Machine Editor</div>
        </div>

        <div class="tab" id="characters">
        </div>

        <div class="tab" id="profile" style="display: none">

        </div>

        <div class="tab editor" id="characterEditor" style="display: none">

        </div>
    </div>

    <div id="loading">
        &nbsp;
    </div>
</body>
</html>
