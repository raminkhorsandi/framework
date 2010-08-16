<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @category    TODO
 * @package     Opus_SolrSearch
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Opus_SolrSearch_Result {

    private $id;
    private $score;
    private $authors;
    private $titleDeu;
    private $titleEng;
    private $year;
    private $abstractDeu;
    private $abstractEng;

    public function  __construct() {
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getScore() {
        return $this->score;
    }

    public function setScore($score) {
        $this->score = $score;
    }

    public function getAuthors() {
        return $this->authors;
    }

    public function setAuthors($authors) {
        if (!is_array($authors)) {
            $this->authors = array($authors);
        }
        else {
            $this->authors = $authors;
        }
    }

    public function getTitleDeu() {
        return $this->titleDeu;
    }

    public function setTitleDeu($titleDeu) {
        $this->titleDeu = $titleDeu;
    }

    public function getTitleEng() {
        return $this->titleEng;
    }

    public function setTitleEng($titleEng) {
        $this->titleEng = $titleEng;
    }

    public function getYear() {
        return $this->year;
    }

    public function setYear($year) {
        $this->year = $year;
    }

    public function getAbstractDeu() {
        return $this->abstractDeu;
    }

    public function setAbstractDeu($abstractDeu) {
        $this->abstractDeu = $abstractDeu;
    }

    public function getAbstractEng() {
        return $this->abstractEng;
    }

    public function setAbstractEng($abstractEng) {
        $this->abstractEng = $abstractEng;
    }


}
?>