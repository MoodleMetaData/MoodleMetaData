<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * A custom renderer class that extends the plugin_renderer_base and
 * is used by the generic library block.
 *
 * @package library
 * @author Josh Stagg jstagg@ualberta.ca
 **/
class block_library_renderer extends plugin_renderer_base {

    public function render_library_content($config, $id) {

        $this->page->requires->js('/blocks/library/module.js');
        $html = html_writer::start_div('library-block');

        // Build the Header.
        $headerimage = html_writer::empty_tag('img', array(
                'src' => '/blocks/library/pix/library-banner.jpg'
            )
        );
        $header = html_writer::link(get_string('universityhomelink', 'block_library'), $headerimage, array('target' => '_blank'));
        $header .= html_writer::link(get_string('libraryloginlink', 'block_library'),
            get_string('librarylogin', 'block_library'),
            array('class' => 'my-account', 'target' => '_blank'));

        // Library search.
        if ( empty($config) || $config->enablesearch == 1) {
            $search = '';
            $search .= html_writer::start_tag('form',
                array('onsubmit' => 'return M.block_library.ebscoHostSearchGo(this);',
                    'id' => 'ebscohostCustomSearchBox',
                    'class' => 'searcher',
                    'name' => 'ebscoSearch',
                    'method' => 'post'
                ));
            $search .= html_writer::tag('input', '', array('id' => 'ebscohostwindow',
                'name' => 'ebscohostwindow',
                'type' => 'hidden',
                'value' => '0'));
            $search .= html_writer::tag('input', '', array('id' => 'ebscohosturl',
                'name' => 'ebscohosturl',
                'type' => 'hidden',
                'value' => get_string('ebscohosturl', 'block_library')));
            $search .= html_writer::tag('input', '', array('id' => 'ebscohostsearchsrc',
                'name' => 'ebscohostsearchsrc',
                'type' => 'hidden',
                'value' => 'db'));
            $search .= html_writer::tag('input', '', array('id' => 'ebscohostsearchmode',
                'name' => 'ebscohostsearchmode',
                'type' => 'hidden',
                'value' => '+'));
            $search .= html_writer::tag('input', '', array('id' => 'ebscohostkeywords',
                'name' => 'ebscohostkeywords',
                'type' => 'hidden'));
            $search .= html_writer::tag('input', '', array('id' => 'oversizeNiceInput',
                'name' => 'ebscohostsearchtext',
                'type' => 'text',
                'class' => 'oversize input-text front',
                'title' => get_string('searchtip', 'block_library'),
                'placeholder' => get_string('findbooks', 'block_library')));
            $search .= html_writer::tag('button', get_string('searchbuttontext', 'block_library'), array('type' => 'submit',
                'value' => 'Search',
                'class' => 'lib_button mleft',
                'onclick' => '_gaq.push([\'_trackEvent\', \'eds\', \'clicked\'])'));
            $search .= html_writer::end_tag('form');
        }

        $html .= $header;
        $html .= $search;

        //  Library Links.
        if ( empty($config) || $config->enablelibrarylinks == 1) {
            $llinks = '';
            $llinks .= html_writer::tag('div', get_string('library_links', 'block_library'), array('class' => 'library-links'));
            $html .= $llinks;
        }

        // Course links.
        if ( !empty($config->enablecourselinks) && $config->enablecourselinks == 1 && !empty($config->rawhtml['text']) ) {
            $clinks = '';
            $clinks .= html_writer::start_tag('div');
            $clinks .= html_writer::tag('p',  get_string( 'librarycourse', 'block_library' ),
                array('class' => 'library-coursetitle'));
            $clinks .= $config->rawhtml['text'];
            $clinks .= html_writer::end_tag('div');
            $html .= $clinks;
        }

        // Build the footer content.
        $listitems = array();
        $askcontent = '';

        if ( empty($config) || $config->enableaskbubble == 1 ) {
            $listitems[] = html_writer::link(get_string('asklibrarystafflink', 'block_library'),
                get_string('asklibrarystaff', 'block_library'),
                array('class' => 'ask-main', 'target' => '_blank'));
            $listitems[] = html_writer::link(get_string('asklibrarystafflink', 'block_library'),
                get_string('textstaff', 'block_library'),
                array('target' => '_blank'));
            $listitems[] = html_writer::link(get_string('chatwithstafflink', 'block_library'),
                get_string('chatwithstaff', 'block_library'),
                array('target' => '_blank'));
            $listitems[] = html_writer::link(get_string('emailstafflink', 'block_library'),
                get_string('emailstaff', 'block_library'),
                array('target' => '_blank'));
            $listitems[] = html_writer::link(get_string('asklibrarystafflink', 'block_library'),
                get_string('phonestaff', 'block_library'),
                array('class' => 'last', 'target' => '_blank'));

            $askcontent = html_writer::alist($listitems, array('class' => 'ask'));
        }
        $footer = html_writer::tag('div', $askcontent, array('class' => 'library-bottom'));

        $html .= $footer;
        $html .= html_writer::end_div();

        return $html;
    }

}