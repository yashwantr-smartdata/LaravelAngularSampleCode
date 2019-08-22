<?php
namespace App\Repository;

use App\Repository\RepositoryInterface;
use App\User;

class UserRepository implements RepositoryInterface
{
    private $model;

    /**
    * Constructor function
    * @param $User(Model)
    * @return
    * Created By: Yashwant Rautela
    * Created At: 19July2019 
    */
    public function __construct(User $User)
    {
        # code...
        $this->model = $User;
    }

    /**
    * Function To create and update data
    * @param $condition(Array), $parameters(Array)
    * @return $resultSet(object)
    * Created By: Yashwant Rautela
    * Created At: 19July2019 
    */
    public function createUpdateData($condition, $parameters)
    {
        # code...
        return $resultSet = $this->model->updateOrCreate($condition, $parameters);
    }

    /**
    * Function to create data
    * @param $data(Array)
    * @return $resultSet(object)
    * Created By: Yashwant Rautela
    * Created At: 19July2019 
    */
    public function createData($data){
        return $resultSet = $this->model->create($data);
    }

    /**
    * Function to fetch data
    * @param $conditions(Array), $method('first' or 'get'), $withArr(Array of relations), $toArray(boolean)
    * @return $resultSet(object or Array)
    * Created By: Yashwant Rautela
    * Created At: 19July2019 
    */
    public function getData($conditions, $method, $withArr = [],$toArray)
    {
        # code...
        $query = $this->model->whereNotNull('id');

        if (!empty($conditions['id'])) {
            # code...
            $query->where('id', $conditions['id']);
        }

        if (!empty($conditions['email'])) {
            # code...
            $query->where('email', $conditions['email']);
        }

        if (!empty($withArr)) {
            $query->with($withArr);
        }

        $resultSet = $query->orderBy('created_at', 'desc')->$method();

        if (!empty($resultSet) && $toArray) {
            # code...
            $resultSet = $resultSet->toArray();
        }

        return $resultSet;
    }
}