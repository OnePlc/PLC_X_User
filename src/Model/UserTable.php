<?php
/**
 * UserTable.php - User Table
 *
 * Table Model for User
 *
 * @category Model
 * @package User
 * @author Verein onePlace
 * @copyright (C) 2020 Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

namespace OnePlace\User\Model;

use Application\Controller\CoreController;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\DbSelect;

class UserTable {
    /**
     * User Table Object
     *
     * @var TableGateway
     * @since 1.0.0
     */
    private $tableGateway;

    /**
     * UserTable constructor.
     *
     * @param TableGateway $tableGateway
     * @since 1.0.0
     */
    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    /**
     * Fetch All Users
     *
     * @param bool $bPaginated paginate results
     * @param array $aWhere filter results
     * @return mixed
     * @since 1.0.0
     */
    public function fetchAll($bPaginated = false,$aWhere = [])
    {
        $oSel = new Select($this->tableGateway->getTable());
        # Build where
        $oWh = new Where();
        foreach(array_keys($aWhere) as $sWh) {
            $bIsLike = stripos($sWh,'-like');
            if($bIsLike === false) {

            } else {
                # its a like
                $oWh->like(substr($sWh,0,strlen($sWh)-strlen('-like')),$aWhere[$sWh].'%');
            }
        }
        $oSel->where($oWh);
        # Return Paginator or Raw ResultSet based on selection
        if ($bPaginated) {
            # Create result set for user entity
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new User($this->tableGateway->getAdapter()));

            # Create a new pagination adapter object
            $oPaginatorAdapter = new DbSelect(
                # our configured select object
                $oSel,
                # the adapter to run it against
                $this->tableGateway->getAdapter(),
                # the result set to hydrate
                $resultSetPrototype
            );
            # Create Paginator with Adapter
            $oPaginator = new Paginator($oPaginatorAdapter);
            return $oPaginator;
        } else {
            $oResults = $this->tableGateway->selectWith($oSel);
            return $oResults;
        }
    }

    /**
     * Fetch Single User
     *
     * @param $id
     * @param string $key
     * @return mixed
     * @since 1.0.0
     */
    public function getSingle($id,$key = 'User_ID') {
        $select = new Select($this->tableGateway->getTable());
        $where = new Where();
        $where->like($key,$id);
        $select->where($where);
        $rowset = $this->tableGateway->selectWith($select);
        $row = $rowset->current();
        if (! $row) {
            throw new \RuntimeException(sprintf(
                'Could not find user with identifier %s',
                $id
            ));
        }

        return $row;
    }

    /**
     * Save User
     *
     * @param User $user
     * @return int id
     * @since 1.0.0
     */
    public function saveSingle(User $user) {
        $data = [
            'username' => $user->username,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'password' => $user->password,
        ];

        $id = (int) $user->id;

        if ($id === 0) {
            $this->tableGateway->insert($data);
            return $this->tableGateway->lastInsertValue;
        }

        try {
            $this->getSingle($id);
        } catch (RuntimeException $e) {
            throw new RuntimeException(sprintf(
                'Cannot update user with identifier %d; does not exist',
                $id
            ));
        }

        $this->tableGateway->update($data, ['User_ID' => $id]);

        return $id;
    }

    /**
     * Generate daily stats for skeleton
     *
     * @since 1.0.5
     */
    public function generateDailyStats() {
        # get all skeletons
        $iTotal = count($this->fetchAll(false));
        # get newly created skeletons
        $iNew = count($this->fetchAll(false,['created_date-like'=>date('Y-m-d',time())]));

        # add statistics
        CoreController::$aCoreTables['core-statistic']->insert([
            'stats_key'=>'user-daily',
            'data'=>json_encode(['new'=>$iNew,'total'=>$iTotal]),
            'date'=>date('Y-m-d H:i:s',time()),
        ]);
    }
}