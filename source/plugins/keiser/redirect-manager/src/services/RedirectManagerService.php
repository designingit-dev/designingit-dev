<?php

namespace keiser\redirectmanager\services;

use craft\base\Component;
use craft\helpers\UrlHelper;
use http\Exception\InvalidArgumentException;
use keiser\redirectmanager\models\RedirectManagerModel;
use keiser\redirectmanager\records\RedirectManagerRecord;

class RedirectManagerService extends Component
{

    /**
     * @param $uri
     * @return array|bool
     * @throws \yii\base\Exception
     */
    public function processRedirect($uri)
    {
        $records = $this->getAllRedirects();

        foreach($records as $record)
        {
            $record = $record->attributes;

            // trim to tolerate whitespace in user entry
            $record['uri'] = trim($record['uri']);

            // type of match. 3 possibilities:
            // standard match (no *, no initial and final #) - regex_match = false
            // regex match (initial and final # (may also contain *)) - regex_match = true
            // wildcard match (no initial and final #, but does have *) - regex_match = true
            $regex_match = false;
            if(preg_match("/^#(.+)#$/", $record['uri'], $matches)) {
                // all set to use the regex
                $regex_match = true;
            } elseif (strpos($record['uri'], "*")) {
                // not necessary to replace / with \/ here, but no harm to it either
                $record['uri'] = "#^".str_replace(array("*","/"), array("(.*)", "\/"), $record['uri']).'#';
                $regex_match = true;
            }
            if ($regex_match) {
                if(preg_match($record['uri'], $uri)){
                    $redirectLocation = preg_replace($record['uri'], $record['location'], $uri);
                    break;
                }
            } else {
                // Standard match
                if ($record['uri'] == $uri)
                {
                    $redirectLocation = $record['location'];
                    break;
                }
            }
        }
        if(isset($redirectLocation)){
            return ["url" => ( strpos($record['location'], "http") === 0 ) ? $redirectLocation : UrlHelper::siteUrl($redirectLocation), "type" => $record['type']];
        }
        return false;
    }

    public function getAllRedirects()
    {
        return RedirectManagerRecord::find()->all();
    }

    public function getRedirectById($id)
    {
        $record = RedirectManagerRecord::findOne($id);
        $model = new RedirectManagerModel();
        $model->id = $record->id;
        $model->uri = $record->uri;
        $model->type = $record->type;
        $model->location = $record->location;
        return $model;
    }

    public function saveRedirect(RedirectManagerModel $model, bool $runValidation = false): bool
    {
        if ($model->id) {
            $record = RedirectManagerRecord::findOne($model->id);

            if (!$record) {
                throw new InvalidArgumentException('No redirect exists with the ID “{id}”', ['id' => $model->id]);
            }
        } else {
            $record = new RedirectManagerRecord();
        }

        $record->uri = $model->uri;
        $record->location = $model->location;
        $record->type = $model->type;

        if ($record->save(false)) {
            // update id on model (for new records)
            if(!$model->id){
                $model->id = $record->id;
            }
            return true;
        } else {
            $model->addErrors($record->getErrors());
            return false;
        }
    }

    public function deleteRedirectById($id)
    {
        $record = RedirectManagerRecord::findOne($id);
        if($record){
            $record->delete();
        }
    }
}
