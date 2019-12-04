<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;

/**
 * Articles Controller
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 *
 * @method \App\Model\Entity\Article[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ArticlesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $articles = $this->paginate($this->Articles, [
            'fields' => [
                'Articles.id',
                'Articles.title',
                'Articles.description',
                'Articles.author_name',
                'Articles.publish_date',
                'Articles__count_tags' => 'count(ArticleTagRel.article_id)',
            ],
            'join' => [
                'ArticleTagRel' => [
                    'table' => 'article_tag_rel',
                    'type' => 'LEFT',
                    'conditions' => [
                        'ArticleTagRel.article_id = Articles.id'
                    ],
                ],
            ],
            'contain' => ['Tags']
        ]);

        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'collection' => $articles
            ]));
    }

    /**
     * View method
     *
     * @param string|null $id Article id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $article = $this->Articles->get($id, [
            'contain' => ['Tags']
        ]);
        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'model' => $article
            ]));
//        $this->set('article', $article);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $article = $this->Articles->newEntity();
        if ($this->request->is('post')) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());

            $tags = [];
            foreach ($this->request->getData('tags', []) as $tag) {
                $tagModel = $this->Articles->Tags->findByName($tag)->first();
                if (!$tagModel) {
                    $tagModel = $this->Articles->Tags->newEntity();
                    $tagModel->name = $tag;
                    $tags[] = $tagModel;
                }

            }
            $article->tags = $tags;

            if ($this->Articles->save($article)) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => true,
                        'model' => $article
                    ]));
            } else {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'errors' => $article->getErrors()
                    ]));
            }
        }
    }

    /**
     * Edit method
     *
     * @param string|null $id Article id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $article = $this->Articles->get($id, [
            'contain'=>['Tags']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());

            $tags = [];
            foreach ($this->request->getData('tags', []) as $tag) {

                $tagModel = $this->Articles->Tags->findByName($tag)->first();
                if (!$tagModel) {
                    $tagModel = $this->Articles->Tags->newEntity();
                    $tagModel->name = $tag;
                }
                $tags[] = $tagModel;

            }
            $article->tags = $tags;

            if ($this->Articles->save($article)) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => true,
                        'model' => $article
                    ]));
            }else{
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'errors' => $article->getErrors()
                    ]));
            }
        }
    }

    /**
     * Delete method
     *
     * @param string|null $id Article id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $article = $this->Articles->get($id);
        if ($this->Articles->delete($article)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
//                    'model' => $article
                ]));
//            $this->Flash->success(__('The article has been deleted.'));
        } else {
//            $this->Flash->error(__('The article could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
