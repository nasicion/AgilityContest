<?php
class INTL3 extends Federations {

    function __construct() {
        parent::__construct();
        // combine global data with specific data for this federation
        $this->config= array_merge ($this->config, array(
            'ID'    => 9,
            'Name'  => 'Intl-3',
            'ClassName' => get_class($this),
            'LongName' => 'International Contest - 3 heights',
            // use basename http absolute path for icons, as need to be used in client side
            'OrganizerLogo'        => 'fciawc2016.png',
            'Logo'        => 'rsce.png',
            'ParentLogo'  => 'fci.png',
            'WebURL' => 'http://www.fci.org',
            'ParentWebURL' => 'http://www.fci.org',
            'Email' => 'info@fci.be',
            'Heights' => 3,
            'Grades' => 3,
            'Games' => 0,
            'International' => 1,
            'WideLicense' => false, // some federations need extra print space to show license ID
            'RoundsG1' => 2,
            'Recorridos' => array(_('Common course'),_("Large / Med + Small"),_("Separate courses")),
            'ListaGradosShort' => array(
                '-' => '-',
                'Jr' => 'Jr.',
                'Sr' => 'Sr.',
                'GI' => 'A1',
                'GII'=> 'A2',
                'GIII' => 'A3',
                'P.A.' => 'A0',
                'P.B.' => 'T.d.' // "Test dog"
            ),
            'ListaGrados'    => array (
                '-' => 'Individual',
                'Jr' => 'Junior',
                'Sr' => 'Senior',
                'GI' => 'Grade I',
                'GII'=> 'Grade II',
                'GIII' => 'Grade III',
                'P.A.' => 'Pre-Agility',
                'P.B.' => 'Test dog'
            ),
            'ListaCategoriasShort' => array (
                '-' => '-',
                // 'E' => 'Extra',
                'L' => 'Large',
                'M' => 'Med',
                'S' => 'Small',
                // 'T' => 'Tiny'
            ),
            'ListaCategorias' => array (
                '-' => '-',
                // 'E' => 'Extra',
                'L' => 'Large',
                'M' => 'Medium',
                'S' => 'Small',
                // 'T' => 'Tiny'
            ),
            'ListaCatGuias' => array (
                '-' => 'Not specified',
                'I' => 'Children',
                'J' => 'Junior',
                'A' => 'Adult',
                'S' => 'Senior',
                'R' => 'Retired',
                'P' => 'Para-Agility',
            ),
            'InfoManga' => array(
                array('L' => _('Large'),         'M' => _('Medium'),         'S' => _('Small'), 'T' => ''), // separate courses
                array('L' => _('Large'),         'M' => _('Medium+Small'),   'S' => '',         'T' => ''), // mixed courses
                array('L' => _('Common course'), 'M' => '',                  'S' => '',         'T' => '') // common
            ),
            'Modes' => array(array(/* separado */ 0, 1, 2, -1), array(/* mixto */ 0, 3, 3, -1), array(/* conjunto */ 4, 4, 4, -1 )),
            'ModeStrings' => array( // text to be shown on each category
                array(/* separado */ "Large", "Medium", "Small", "Invalid"),
                array(/* mixto */ "Large", "Medium+Small", "Medium+Small", "Invalid"),
                array(/* conjunto */ "Common course", "Common course", "Common course", "Invalid")
            ),
            'IndexedModes' => array ( // modes 5 to 8 are invalid in this federation
                "Large", "Medium", "Small", "Medium+Small", "Conjunta L/M/S", "Tiny", "Large+Medium", "Small+Tiny", "Common L/M/S/T"
            ),
            'IndexedModeStrings' => array(
                "-" => "",
                "L"=>"Large",
                "M"=>"Medium",
                "S"=>"Small",
                "T"=>"Tiny", // invalid
                "LM"=>"Large/Medium", // invalid
                "ST"=>"Small/Tiny", // invalid
                "MS"=>"Medium/Small",
                "LMS" => 'Common LMS',
                "LMST" => 'Common LMST',
                "-LMST" => ''
            )
        ));
    }

}
?>