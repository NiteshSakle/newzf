<?php
 namespace Album\Controller;

use Zend\Mvc\Controller\AbstractActionController;
 use Zend\View\Model\ViewModel;
 use Album\Model\Album;
 use Album\Form\AlbumForm;
 use Album\Form\SearchForm;
 use Zend\Db\Sql\Select;
 use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Iterator as paginatorIterator;

 class AlbumController extends AbstractActionController
 {
     protected $albumTable;
     public function indexAction()
     {
            $searchform = new SearchForm();
            $searchform->get('submit')->setValue('search'); 
            $select = new Select();
            $order_by = $this->params()->fromRoute('order_by') ?
            $this->params()->fromRoute('order_by') : 'id';
            $order = $this->params()->fromRoute('order') ?
                     $this->params()->fromRoute('order') : Select::ORDER_ASCENDING;
            $page = $this->params()->fromRoute('page') ? (int) $this->params()->fromRoute('page') : 1;
            $request = $this->getRequest();
            if ($request->isGet()) {
                $formdata    = (array) $request->getQuery();
                $search_data = array();
                foreach ($formdata as $key => $value) {
                 if ($key != 'submit') {
                    if (!empty($value)) {
                        $search_data[$key] = $value;
                    }
                }
            }
            if (!empty($search_data)) {
                $search = $search_data;
            }
            $searchform->setData($formdata);
            }
            
             $search_by = $this->params()->fromQuery() ?
                $this->params()->fromQuery() : '';
            $albums = $this->getAlbumTable()->fetchAll($order_by,$order,$search,$select);
            $totalRecord  = $albums->count();
            $albums->current();
            
            $paginator=new Paginator(new paginatorIterator($albums));
            $paginator->setCurrentPageNumber($page)
                      ->setItemCountPerPage(10)
                      ->setPageRange(7);
            return new ViewModel(array(
                    'search_by'=> $search_by,
                    'order_by' => $order_by,
                    'order' => $order,
                    'page' => $page,
                    'paginator' => $paginator,
                    'pageAction' => 'album',
                    'form'       => $searchform,
                    'totalRecord' => $totalRecord
            ));
     }
     
     
     public function addAction()
     {
			$form = new AlbumForm();
			$form->get('submit')->setValue('Add');
			$request = $this->getRequest();                    
			if ($request->isPost()) 
			{
				$album = new Album();
				$form->setInputFilter($album->getInputFilter());
				$form->setData($request->getPost());
				
				if ($form->isValid())
				{
					$album->exchangeArray($form->getData());
					$this->getAlbumTable()->saveAlbum($album);
					 $this->flashMessenger()->addMessage('Added Successfully...');
					return $this->redirect()->toRoute('album');
				}
				else
				{
					$this->flashMessenger()->addMessage('Failed to Add...!!');
				}
			}
			return array('form' => $form);
	}

     public function editAction()
     {
                    $id = (int) $this->params()->fromRoute('id', 0);
                    if (!$id) 
                    {
                        return $this->redirect()->toRoute('album', array('action' => 'add'));
                    }
                    try 
					{
                        $album = $this->getAlbumTable()->getAlbum($id);
                    }
                    catch (\Exception $ex) 
                    {
                        return $this->redirect()->toRoute('album', array('action' => 'index'));
                    }
                    $form  = new AlbumForm();
                    $form->bind($album);
                    $form->get('submit')->setAttribute('value', 'Edit');
                    $request = $this->getRequest();
                    if ($request->isPost()) 
                    {
                        $form->setInputFilter($album->getInputFilter());
                        $form->setData($request->getPost());

                        if ($form->isValid()) 
                        {
                            $this->getAlbumTable()->saveAlbum($album);
                            $this->flashMessenger()->addMessage('Edited Successfully...');
                            return $this->redirect()->toRoute('album');
                        }
                        else
                        {
                            $this->flashMessenger()->addMessage('Failed to Edit...!!');
                        }
                    }
                    return array(
                        'id' => $id,
                        'form' => $form,
                    );
                    
        }

     public function deleteAction()
     {
                    $id = (int) $this->params()->fromRoute('id', 0);
                    if (!$id) 
                    {
                        return $this->redirect()->toRoute('album');
                    }   
                    $request = $this->getRequest();
                    if ($request->isPost()) 
                    {
                        $del = $request->getPost('del', 'No');
                        if ($del == 'Yes') 
                        {
                            $this->flashMessenger()->addMessage('Deleted Successfully...');
                            $id = (int) $request->getPost('id');
                            $this->getAlbumTable()->deleteAlbum($id);
                        }
                        else
                        {
                            $this->flashMessenger()->addMessage('Failed to Delete...!!');
                        }
                    }
         
				return array(
					'id'    => $id,
					'album' => $this->getAlbumTable()->getAlbum($id)
				);
      }
     public function cancelAction()
     {
         
     }
     
     public function getAlbumTable()
     {
         if (!$this->albumTable) {
             $sm = $this->getServiceLocator();
             $this->albumTable = $sm->get('Album\Model\AlbumTable');
         }
         return $this->albumTable;
     }
     
 }

