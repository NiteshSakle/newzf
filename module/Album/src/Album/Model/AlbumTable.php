<?php
 namespace Album\Model;

 use Zend\Db\TableGateway\TableGateway;
 use Zend\Db\Sql\Select;
 use Zend\Db\Sql\Where;
 use Zend\Db\Sql\Predicate\Like;

 class AlbumTable
 {
     protected $tableGateway;

     public function __construct(TableGateway $tableGateway)
     {
         $this->tableGateway = $tableGateway;
     }

     public function fetchAll($order_by, $order, $search_by, Select $select = null)
     {
         if (null === $select)
            $select = new Select();
         $table=  $this->tableGateway->table;
         $select->from($table);
            $where    = new Where();
            $formdata=array();
            if (!empty($search_by))
            {
                $formdata = $search_by;
                if (!empty($formdata['search'])) 
                {
                    $where->addPredicate(new Like('artist','%' .$formdata['search'] . '%'))->orPredicate(new Like('title', '%' . $formdata['search'] . '%'));
                }
          
            }
            if (!empty($where)) {
                $select->where($where);
            }
            
            $select->order($order_by . ' ' . $order);
            
        $resultSet = $this->tableGateway->selectWith($select);
        $resultSet->buffer();
        return $resultSet;
     }

     public function getAlbum($id)
     {
         $id  = (int) $id;
         $rowset = $this->tableGateway->select(array('id' => $id));
         $row = $rowset->current();
         if (!$row) {
             throw new \Exception("Could not find row $id");
         }
         return $row;
     }

     public function saveAlbum(Album $album)
     {
         $data = array(
             'artist' => $album->artist,
             'title'  => $album->title,
         );

         $id = (int) $album->id;
         if ($id == 0) {
             $this->tableGateway->insert($data);
         } else {
             if ($this->getAlbum($id)) {
                 $this->tableGateway->update($data, array('id' => $id));
             } else {
                 throw new \Exception('Album id does not exist');
             }
         }
     }

     public function deleteAlbum($id)
     {
         $this->tableGateway->delete(array('id' => (int) $id));
     }
 }
?>
