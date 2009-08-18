<?php
    /*
    RSDB - ReactOS Support Database
    Copyright (C) 2009  Danny G�tte <dangerground@web.de>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
    */


class HTML_List extends HTML
{

  private $where = '';
  private $from = '';
  private $params = array();
  private $select = '';
  private $showColumn = array();


  public function __construct($filter='')
  {
    $this->buildQuery($filter);
    parent::__construct();
  }


  protected function body ()
  {
    $limit = 30;
    
    if (isset($_GET['offset'])) {
      $offset = intval($_GET['offset']);
    }
    else {
      $offset = 0;
    }

    echo '
      <h1>Compatability Database &gt; browse by name</h1>';
      
    
    if (isset($_GET['letter'])) {
      $this->naviLetter($_GET['letter']);
    }
    elseif (isset($_GET['cat'])) {
      $this->naviCategory($_GET['cat']);
    }
    elseif (isset($_GET['tag']) && $_GET['tag'] == '*') {
      $this->naviTags();
    }

    elseif (isset($_GET['filter'])) {
      $this->naviCustom();
    }

    if (!isset($_GET['tag']) || $_GET['tag'] != '*') {
      
      $stmt=CDBConnection::getInstance()->prepare("SELECT COUNT(*) FROM ".CDBT_ENTRIES." e ".$this->from." WHERE e.visible IS TRUE ".$this->where);
      foreach ($this->params as $param) {
        $stmt->bindValue($param[0],$param[1],$param[2]);
      }
      $stmt->execute();
      $entries_count = $stmt->fetchColumn();

      if ($entries_count > 0) {
        echo '
          <h2>Entries</h2>
          <table class="rtable" cellspacing="0" cellpadding="0">
            <thead>
              <tr>
                <th>&nbsp;</th>
                <th>Application</th>';
        if (count($this->showColumn) > 0) {
          foreach ($this->showColumn as $column) {
            echo '<th>'.$column['description'].'</th>';
          }
        }
        echo '
                <th>Last modified</th>
              </tr>
            </thead>
            <tbody>';

        reset($this->params);
        $stmt=CDBConnection::getInstance()->prepare("SELECT e.id, e.name, e.modified, (SELECT works FROM ".CDBT_REPORTS." WHERE entry_id=e.id ORDER BY created DESC LIMIT 1) AS works ".$this->select." FROM ".CDBT_ENTRIES." e ".$this->from." WHERE e.visible IS TRUE ".$this->where." ORDER BY e.name ASC LIMIT :limit OFFSET :offset");
        foreach ($this->params as $param) {
          $stmt->bindValue($param[0],$param[1],$param[2]);
        }
        $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $x=0;
        while ($entry=$stmt->fetch(PDO::FETCH_ASSOC)) {
          ++$x;

          $stmt_ver=CDBConnection::getInstance()->prepare("SELECT id, version FROM ".CDBT_VERSIONS." WHERE entry_id=:entry_id ORDER BY version DESC");
          $stmt_ver->bindParam('entry_id',$entry['id'],PDO::PARAM_STR);
          $stmt_ver->execute();
          $versions = $stmt_ver->fetchAll(PDO::FETCH_ASSOC);
          
          // display entry only if it has also at least one version information
          if (count($versions) > 0) {
            echo '
              <tr class="row'.($x%2+1).'" id="tr'.$x.'" title="'.($entry['works'] == 'full' ? 'stable' : ($entry['works'] == 'part' ? 'unstable' : 'crash')).'">
                <td class="first '.($entry['works'] == 'full' ? 'stable' : ($entry['works'] == 'part' ? 'unstable' : 'crash')).'">&nbsp;</td>
                <td onmouseover="'."this.className=document.getElementById('tr".$x."').title;".'" onmouseout="this.className=\'\';">';

            // just one version stored
            if (count($versions) == 1) {
              echo '
                  <a href="?show=version&amp;id='.$versions[0]['id'].'">'.htmlspecialchars($entry['name']).' '.$versions[0]['version'].'</a>';
            }

            // show all app version
            else {
              echo '
                <a href="?show=entry&amp;id='.$entry['id'].'">'.htmlspecialchars($entry['name']).'</a>
                <ul>';

              // list all app versions
              foreach ($versions as $version) {
                echo '
                  <li><a href="?show=version&amp;id='.$version['id'].'">'.$version['version'].'</a></li>';
              }
              
              echo '
                </ul>';
            }

            echo '
                </td>';
            if (count($this->showColumn) > 0) {
              foreach ($this->showColumn as $column) {
                echo '<th>'.$entry[$column['field']].'</th>';
              }
            }
            echo '
                <td class="modified">'.$entry['modified'].'</td>
              </tr>';
          }
        }

        echo '
            </tbody>
          </table>';
      }

      if ($entries_count > $limit) {
        echo '<div>Navigation:';
        $to = ceil($entries_count/(float)$limit);
        for ($i=1; $i <= $to ; ++$i) {
          if ($offset==($i-1)*$limit) {
            echo '<strong>['.$i.']</strong>';
          }
          else {
            echo '<a href="?show=list'.(isset($_GET['letter']) ? '&amp;letter='.$_GET['letter']:'').'&amp;offset='.(($i-1)*$limit).'">'.$i.'</a>';
          }
        }
        echo '</div>';
      }
    }
  } // end of member function body



  private function naviLetter( $letter )
  {
    echo '<div id="letternavi"><h2>Letter Navigation</h2>';
    for ($i=ord('a'); $i <= ord('z') ; ++$i) {
      if (isset($letter) && chr($i)==$letter) {
        echo '<strong>['.strtoupper(chr($i)).']</strong>';
      }
      else {
        echo '<a href="?show=list&amp;letter='.chr($i).'">'.strtoupper(chr($i)).'</a>';
      }
    }
    echo '</div>';
  }



  private function naviCategory( $category_id )
  {

    // show root
    echo '
      <ul id="breadcrumb">
        <li style="float: left;"><a href="?show=list&amp;cat=0">Root</a></li>';


    // show current path
    $stmt=CDBConnection::getInstance()->prepare("SELECT name, id, parent FROM ".CDBT_CATEGORIES." WHERE id=:cat_id AND visible IS TRUE");
    $stmt->bindParam('cat_id',$category_id,PDO::PARAM_INT);
    $stmt->execute();

    // get output in reversed order
    $output = '';
    while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $output = '
        <li>&rarr; <a href="?show=list&amp;cat='.$category['id'].'">'.htmlspecialchars($category['name']).'</a></li>'.$output;

      if ($category['parent'] > 0) {
        $stmt->bindParam('cat_id',$category['parent'],PDO::PARAM_INT);
        $stmt->execute();
      }
    } // end while

    echo $output.'
      </ul>';
  
    $stmt=CDBConnection::getInstance()->prepare("SELECT COUNT(*) FROM ".CDBT_CATEGORIES." p WHERE parent=:category_id");
    $stmt->bindParam('category_id',$category_id,PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->fetchColumn() > 0) {
    
      echo '
        <div style="margin-bottom: 3em;clear:both;">
          <h2>Category Navigation</h2>
          <table class="rtable" cellspacing="0" cellpadding="0">
            <thead>
              <tr>
                <th>Name</th>
                <th>Subcategories</th>
                <th>direct&nbsp;entries</th>
              </tr>
            </thead>
            <tbody>';

      $stmt=CDBConnection::getInstance()->prepare("SELECT id, name, (SELECT COUNT(*) FROM ".CDBT_CATEGORIES." WHERE parent=p.id) AS subcategories, (SELECT COUNT(*) FROM ".CDBT_ENTRIES." WHERE category_id=p.id) AS entries FROM ".CDBT_CATEGORIES." p WHERE parent=:category_id ORDER BY name ASC");
      $stmt->bindParam('category_id',$category_id,PDO::PARAM_STR);
      $stmt->execute();
      $x=0;
      while ($category=$stmt->fetch(PDO::FETCH_ASSOC)) {
        ++$x;

        echo '
          <tr class="row'.($x%2+1).'">
            <td><a href="?show=list&amp;cat='.$category['id'].'">'.$category['name'].'</a></td>
            <td>'.$category['subcategories'].'</td>
            <td>'.$category['entries'].'</td>
          </tr>';
      }

      echo '
            </tbody>
          </table>
        </div>';
    }
  }




  private function naviTags( )
  {
    $stmt=CDBConnection::getInstance()->prepare("SELECT MAX((SELECT COUNT(*) FROM ".CDBT_TAGGED." WHERE tag_id=t.id)) AS max FROM ".CDBT_TAGS." t");
    $stmt->execute();
    $max = $stmt->fetchColumn();

    echo '
      <h2>Navigate by Tag</h2>
      <div style="margin-bottom: 3em;text-align:left;">';
    
    $stmt=CDBConnection::getInstance()->prepare("SELECT id, name, (SELECT COUNT(*) FROM ".CDBT_TAGGED." WHERE tag_id=t.id) AS count FROM ".CDBT_TAGS." t ORDER BY name ASC");
    $stmt->execute();
    while ($tag=$stmt->fetch(PDO::FETCH_ASSOC)) {

      echo '<a style="margin-right: 20px; font-size: '.(((int)$tag['count']/(float)$max*2.0)+0.9).'em;" href="?show=list&amp;tag='.htmlspecialchars($tag['name']).'">'.htmlspecialchars($tag['name']).'</a>';
    }
    echo '
      </div>';
  }




  private function naviCustom( )
  {
    echo '
      <form action="" method="get">
        <fieldset>
          <legend></legend>
        </fieldset>
      </form>';
  }


  private function buildQuery($filter)
  {
    if ($filter == '') {
      return false;
    }
    elseif (!strstr($filter, '_')) {
      return false;
    }
  
    $filters = explode('|',$filter);
    foreach ($filters as $filter) {
      $part = explode('_',$filter);

      $this->where .= ' AND ';

      switch ($part[0]) {
        
        // starts with:
        case 's':
          $this->where .= " e.name LIKE :starts_with ";
          $this->params[] = array('starts_with',$part[2].'%',PDO::PARAM_STR);
          break;

        // contains
        case 'h':
          $this->where .= " e.name LIKE :contains ";
          $this->params[] = array('contains','%'.$part[2].'%',PDO::PARAM_STR);
          break;

        // ends with
        case 'e':
          $this->where .= " e.name LIKE :ends_with ";
          $this->params[] = array('ends_with','%'.$part[2],PDO::PARAM_STR);
          break;

        // tagged with
        case 't':
          $this->where .= " t.name = :tagname ";
          $this->from .= " JOIN ".CDBT_TAGGED." tr ON tr.entry_id=e.id ";
          $this->from .= " JOIN ".CDBT_TAGS." t ON t.id=tr.tag_id ";
          $this->params[] = array('tagname',$part[2],PDO::PARAM_STR);
          break;

        // show additional columns
        case 's':
          switch ($part[2]) {
            case 'category';
            $this->select .= " (SELECT name FROM ".CDBT_CATEGORIES." WHERE id=e.category_id) AS category";
            $this->showColumn[] = array('field'=>'category','description'=>'Category');
          }
          break;

        // category
        case 'c':
          if ($part[1] == 'is') {
            $this->where .= " e.category_id = :category ";
          }
          else {
            $this->where .= " e.category_id != :category ";
          }
          $this->from .= " JOIN ".CDBT_CATEGORIES." c ON c.id=e.category_id ";
          $this->params[] = array('category',$part[2],PDO::PARAM_STR);
          break;
      }
    }
  } // end of member function buildQuery
  
} // end HTML_List
?>
