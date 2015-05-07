<!-- Sidebar Menu -->
<nav class="sidebar" id="jsi-nav" data-sidebar-options="">
    <div class="logo">
        <a data-href="<?php echo $this->config->item('site_url'); ?>dashboard" onclick="urlParse(this)">
            <img src="<?php echo $this->config->item('images_url'); ?>logo_black.png" alt="" class="animated rubberBand"/>
        </a>
    </div>
    <div class="logo_small">
        <a data-href="<?php echo $this->config->item('site_url'); ?>dashboard" onclick="urlParse(this)">
            <img src="<?php echo $this->config->item('images_url'); ?>logo_small.png" alt=""/>
        </a>
    </div>
    <div class="sidebar-list" >
        <div id='cssmenu'>
            <ul>
                <?php
                $frontPanelMenu = $this->general->getFrontPanelMenu();

                $html = '';
                $currentUrl = basename($this->uri->uri_string);

                $selected_menu = 'dashboard';

                foreach ($frontPanelMenu as $key => $value) {
                    $vSelectedMenu = explode(',', $value['vSelectedMenu']);

                    $frontPanelSubMenu = $this->general->getFrontPanelMenu($value['iModuleId']);
                    $selected_menu = (strtolower($currentUrl) == strtolower($value['main_menu_code'])) ? $value['main_menu_code'] : $selected_menu;

                    $url = (stristr($value['vURL'], 'javascript') === false) ? $this->config->item('site_url') : '';
                    $onclickfunction = ($value['vURL'] != 'javascript:void(0)') ? 'onclick="urlParse(this)"' : '';
                    $html .= '
                    <li class="' . (($selected_menu == $value['main_menu_code']) ? 'active_new' : '') . ((is_array($frontPanelSubMenu) && count($frontPanelSubMenu) > 0) ? 'has-sub' : '') . '">
                        <a data-href="' . $url . $value['vURL'] . '" class="accordion ' . (in_array($currentUrl, $vSelectedMenu) ? 'current now' : '') . '" id="' . $value['main_menu_code'] . '" data-main="' . $value['main_menu_code'] . '" ' . $onclickfunction . ' data-select="'.$value['vSelectedMenu'].'">
                            <div class="Icons nav_icons ' . $value['vImage'] . ' ' . $value['vImage'] . '_hover animated bounceInLeft"></div>
                            <div class="tooltip_main">' . $value['vMenuDisplay'] . '</div>
                            <span class="animated bounceInRight">' . $value['vMenuDisplay'] . '</span>
                        </a>';

                    if (is_array($frontPanelSubMenu) && count($frontPanelSubMenu) > 0) {
                        $html .= '<ul>';
                        foreach ($frontPanelSubMenu as $skey => $svalue) {
                            $vSubSelectedMenu = explode(',', $svalue['vSelectedMenu']);
                            $selected_menu = (strtolower($currentUrl) == strtolower($svalue['vURL'])) ? $svalue['main_menu_code'] : $selected_menu;
                            $suburl = (stristr($svalue['vURL'], 'javascript') === false) ? $this->config->item('site_url') : '';
                            $html .= '<li ' . ($skey == (count($frontPanelSubMenu) - 1) ? 'class="last"' : '') . '>
                            <a data-href="' . $suburl . $svalue['vURL'] . '" data-main="' . $svalue['main_menu_code'] . '"  class="accordion ' . (in_array($currentUrl, $vSubSelectedMenu) ? 'current now"' : '') . '" onclick="urlParse(this)" data-select="'.$svalue['vSelectedMenu'].'">
                                <div class="Icons nav_icons ' . $svalue['vImage'] . ' ' . $svalue['vImage'] . '_hover animated bounceInLeft"></div>
                                <div class="tooltip_main">' . $svalue['vMenuDisplay'] . '</div>
                                <span class="animated bounceInRight">' . $svalue['vMenuDisplay'] . '</span>
                            </a>
                        </li>';
                        }
                        $html .= '</ul>
                    </li>';
                    } else {
                        $html .= '
                    </li>';
                    }
                }
                echo $html;
                ?>
            </ul>
        </div>
    </div>
</nav>
<!-- // Sidebar Menu END -->
<script>
    $(document).ready(function () {
        init_sidebar();
        default_sidebar();
    });

</script>