<?php


if (!defined('PDF_CUSTOM_FONT_PATH')) {
    /** Defines the site-specific location of fonts. */
    define('PDF_CUSTOM_FONT_PATH', './fonts/');
}

if (!defined('PDF_DEFAULT_FONT')) {
    /** Default font to be used. */
    define('PDF_DEFAULT_FONT', 'FreeSerif');
}

/** tell tcpdf it is configured here instead of in its own config file */
define('K_TCPDF_EXTERNAL_CONFIG', 1);

// The configuration constants needed by tcpdf follow

/**
 * Init K_PATH_FONTS and PDF_FONT_NAME_MAIN constant.
 *
 * Unfortunately this hack is necessary because the constants need
 * to be defined before inclusion of the tcpdf.php file.
 */
function tcpdf_init_k_font_path() {

    $defaultfonts = './tcpdf/fonts/';

    if (!defined('K_PATH_FONTS')) {
        if (is_dir(PDF_CUSTOM_FONT_PATH)) {
            // NOTE:
            //   There used to be an option to have just one file and having it set as default
            //   but that does not make sense any more because add-ons using standard fonts
            //   would fail very badly, also font families consist of multiple php files for
            //   regular, bold, italic, etc.

            // Check for some standard font files if present and if not do not use the custom path.
            $somestandardfiles = array('courier',  'helvetica', 'times', 'symbol', 'zapfdingbats', 'freeserif', 'freesans');
            $missing = false;
            foreach ($somestandardfiles as $file) {
                if (!file_exists(PDF_CUSTOM_FONT_PATH . $file . '.php')) {
                    $missing = true;
                    break;
                }
            }
            if ($missing) {
                define('K_PATH_FONTS', $defaultfonts);
            } else {
                define('K_PATH_FONTS', PDF_CUSTOM_FONT_PATH);
            }
        } else {
            define('K_PATH_FONTS', $defaultfonts);
        }
    }

    if (!defined('PDF_FONT_NAME_MAIN')) {
        define('PDF_FONT_NAME_MAIN', strtolower(PDF_DEFAULT_FONT));
    }
}
tcpdf_init_k_font_path();

/** tcpdf installation path */
define('K_PATH_MAIN', './tcpdf/');

/** URL path to tcpdf installation folder */
define('K_PATH_URL', '/tcpdf/');

/** cache directory for temporary files (full path) */
define('K_PATH_CACHE', realpath('./tmp/'));

/** images directory */
define('K_PATH_IMAGES', realpath('.'));

/** blank image */
define('K_BLANK_IMAGE', realpath('./spacer.gif'));

/** height of cell repect font height */
define('K_CELL_HEIGHT_RATIO', 1.25);

/** reduction factor for small font */
define('K_SMALL_RATIO', 2/3);

/** Throw exceptions from errors so they can be caught and recovered from. */
define('K_TCPDF_THROW_EXCEPTION_ERROR', true);

require_once(dirname(__FILE__).'/tcpdf/tcpdf.php');

/**
 * Wrapper class that extends TCPDF (lib/tcpdf/tcpdf.php).
 * Moodle customisations are done here.
 *
 * @package     moodlecore
 * @copyright   Vy-Shane Sin Fat
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pdf extends TCPDF {



    /**
     * Class constructor
     *
     * See the parent class documentation for the parameters info.
     */
    public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8') {


        parent::__construct($orientation, $unit, $format, $unicode, $encoding);

        // theses replace the tcpdf's config/lang/ definitions
       // $this->l['w_page']          = get_string('page');
       // $this->l['a_meta_language'] = current_language();
       // $this->l['a_meta_charset']  = 'UTF-8';
       // $this->l['a_meta_dir']      = get_string('thisdirection', 'langconfig');
    }

    /**
     * Send the document to a given destination: string, local file or browser.
     * In the last case, the plug-in may be used (if present) or a download ("Save as" dialog box) may be forced.<br />
     * The method first calls Close() if necessary to terminate the document.
     * @param $name (string) The name of the file when saved. Note that special characters are removed and blanks characters are replaced with the underscore character.
     * @param $dest (string) Destination where to send the document. It can take one of the following values:<ul><li>I: send the file inline to the browser (default). The plug-in is used if available. The name given by name is used when one selects the "Save as" option on the link generating the PDF.</li><li>D: send to the browser and force a file download with the name given by name.</li><li>F: save to a local server file with the name given by name.</li><li>S: return the document as a string (name is ignored).</li><li>FI: equivalent to F + I option</li><li>FD: equivalent to F + D option</li><li>E: return the document as base64 mime multi-part email attachment (RFC 2045)</li></ul>
     * @public
     * @since Moodle 1.0
     * @see Close()
     */
    public function Output($name='doc.pdf', $dest='I') {
        $olddebug = error_reporting(0);
        $result  = parent::output($name, $dest);
        error_reporting($olddebug);
        return $result;
    }

    /**
     * Is this font family one of core fonts?
     * @param string $fontfamily
     * @return bool
     */
    public function is_core_font_family($fontfamily) {
        return isset($this->CoreFonts[$fontfamily]);
    }

    /**
     * Returns list of font families and types of fonts.
     *
     * @return array multidimensional array with font families as keys and B, I, BI and N as values.
     */
    public function get_font_families() {
        $families = array();
        foreach ($this->fontlist as $font) {
            if (strpos($font, 'uni2cid') === 0) {
                // This is not an font file.
                continue;
            }
            if (strpos($font, 'cid0') === 0) {
                // These do not seem to work with utf-8, better ignore them for now.
                continue;
            }
            if (substr($font, -2) === 'bi') {
                $family = substr($font, 0, -2);
                if (in_array($family, $this->fontlist)) {
                    $families[$family]['BI'] = 'BI';
                    continue;
                }
            }
            if (substr($font, -1) === 'i') {
                $family = substr($font, 0, -1);
                if (in_array($family, $this->fontlist)) {
                    $families[$family]['I'] = 'I';
                    continue;
                }
            }
            if (substr($font, -1) === 'b') {
                $family = substr($font, 0, -1);
                if (in_array($family, $this->fontlist)) {
                    $families[$family]['B'] = 'B';
                    continue;
                }
            }
            // This must be a Family or incomplete set of fonts present.
            $families[$font]['R'] = 'R';
        }

        // Sort everything consistently.
        ksort($families);
        foreach ($families as $k => $v) {
            krsort($families[$k]);
        }

        return $families;
    }
}
