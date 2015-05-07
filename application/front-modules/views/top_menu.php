<style>
    .navbar ul.nav{ padding-left: 10px;}
    .navbar ul.nav>li>a { padding-left: 30px;}
    .navbar ul.nav>li>a.glyphicons i:before{ top:11px; color:#727272}
</style>
<div id="top_menu" class="hidden-print hidden-xs  sidebar-white">
    <div class="navbar-collapse collapse">
        <ul class="nav navbar-nav list-unstyled">
            <?php
            for ($i = 0; $i < count($resultMenu); $i++) {
                ?> 
                <li class="<?php echo (isset($rSubMenu[strtolower($resultMenu[$i]['mainMenu'])]) && count($rSubMenu[strtolower($resultMenu[$i]['mainMenu'])]) > 0) ? 'dropdown' : ''; ?> <?php echo (in_array($currentUrl, explode(',', $resultMenu[$i]['vSelecteMenu']))) ? 'active' : ''; ?>">
                    <a <?php echo (isset($rSubMenu[strtolower($resultMenu[$i]['mainMenu'])]) && count($rSubMenu[strtolower($resultMenu[$i]['mainMenu'])]) > 0) ? 'data-toggle="dropdown"' : ''; ?> class="glyphicons <?php echo $resultMenu[$i]['vImage']; ?> dropdown-toggle" href="<?php echo stristr($resultMenu[$i]['vURL'], '#') ? '' : $resultMenu[$i]['vURL']; ?>" title="<?php echo $resultMenu[$i]['vMenuDisplay']; ?>"  <?php echo (isset($rSubMenu[strtolower($resultMenu[$i]['mainMenu'])]) && count($rSubMenu[strtolower($resultMenu[$i]['mainMenu'])]) > 0) ? 'data-toggle="collapse"' : ''; ?> >
                        <i></i>
                        <?php echo $resultMenu[$i]['vMenuDisplay']; ?>
                        <?php echo (isset($rSubMenu[strtolower($resultMenu[$i]['mainMenu'])]) && count($rSubMenu[strtolower($resultMenu[$i]['mainMenu'])]) > 0) ? '<span class="caret"></span>' : ''; ?>
                    </a>                
                    <?php if (isset($rSubMenu[strtolower($resultMenu[$i]['mainMenu'])]) && count($rSubMenu[strtolower($resultMenu[$i]['mainMenu'])]) > 0) { ?>   
                        <ul class="dropdown-menu">
                            <?php foreach ($rSubMenu[strtolower($resultMenu[$i]['mainMenu'])] as $key => $value) { ?>    
                                <li class="<?php echo (in_array($currentUrl, explode(',', $value['vSelecteMenu']))) ? 'active' : ''; ?>">
                                    <a class="glyphicons <?php echo $value['vImage']; ?>" href="<?php echo $value['vURL']; ?>" title="<?php echo $value['vMenuDisplay']; ?>">
                                        <i></i>
                                        <span><?php echo $value['vMenuDisplay']; ?></span>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php } ?>     
                </li>          
            <?php } ?>
        </ul>
    </div>
</div>