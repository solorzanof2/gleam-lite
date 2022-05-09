<?php

namespace emerus\handlers;

use emerus\handlers\query\additionals\Aggregate;
use emerus\handlers\query\additionals\AndOperator;
use emerus\handlers\query\additionals\Condition;
use emerus\handlers\query\additionals\ConditionImpl;
use emerus\handlers\query\additionals\Field;
use emerus\handlers\query\additionals\FieldImpl;
use emerus\handlers\query\DeleteQuery;
use emerus\handlers\query\InsertQuery;
use emerus\handlers\query\SelectQuery;
use emerus\handlers\query\UpdateQuery;

class CriteriaBuilder
{

    const EQUALS = '=';

    const IN = 'in';

    const INSERT = 'query.insert';

    const DELETE = 'query.delete';

    const UPDATE = 'query.update';

    const SELECT = 'query.select';
    
    /**
     * @var \emerus\handlers\query\SelectQuery
     */
    private $selectQuery;

    /**
     * @var \emerus\handlers\query\InsertQuery
     */
    private $insertQuery = null;

    /**
     * @var \emerus\handlers\query\UpdateQuery
     */
    private $updateQuery = null;

    /**
     * @var \emerus\handlers\query\DeleteQuery
     */
    private $deleteQuery = null;

    /**
     * @var string
     */
    private $query;
    
    /**
     * @var string
     */
    private $tablename;
    
    public function __construct( string $tablename )
    {
        $this->tablename = $tablename;
        $this->selectQuery = new SelectQuery( $tablename );
    }
    
    public function select( string $tablename ): CriteriaBuilder
    {
        $this->selectQuery = new SelectQuery( $tablename );
        $this->setQueryMode( self::SELECT );
        return $this;
    }

    public function where( string $fieldname, string $operator, $value ): CriteriaBuilder
    {
        $condition = new ConditionImpl( $operator, self::getField( $fieldname ), $value );
        switch ( $this->query ) {
            case ( self::UPDATE ):
                $this->updateQuery->setWhere( $condition );
                break;
            case ( self::DELETE ):
                $this->deleteQuery->setWhere( $condition );
                break;
            default:
                $this->selectQuery->setWhere( $condition );
                break;
        }
        return $this;
    }

    public function getSql(): string
    {
        // return $this->selectQuery->sql();
        switch ( $this->query ) {
            case ( self::UPDATE ):
                return $this->updateQuery->sql();
                break;
            case ( self::DELETE ):
                return $this->deleteQuery->sql();
                break;
            case ( self::INSERT ):
                return $this->insertQuery->sql();
            default:
                return $this->selectQuery->sql();
                break;
        }
    }

    public function getParameters()
    {
        // return $this->selectQuery->parameters();
        switch ( $this->query ) {
            case ( self::UPDATE ):
                return $this->updateQuery->parameters();
                break;
            case ( self::DELETE ):
                return $this->deleteQuery->parameters();
                break;
            case ( self::INSERT ):
                return $this->insertQuery->parameters();
                break;
            default:
                return $this->selectQuery->parameters();
                break;
        }
    }

    public function addWhereAndEqualsCondition( array $conditions ): CriteriaBuilder
    {
        $collection = [];
        foreach ( $conditions as $field => $value ) {
            $collection[] = new ConditionImpl( self::EQUALS, self::getField( $field ), $value );
        }

        $this->selectQuery->setWhere( $this->getAndOperator( $collection ) );
        return $this;
    }

    public function addWhereAndCondition( array $conditions ): CriteriaBuilder
    {
        switch ( $this->query ){
            case self::DELETE:
                $this->deleteQuery->setWhere( $this->getAndOperator( $conditions ) );
                break;
            default:
                $this->selectQuery->setWhere( $this->getAndOperator( $conditions ) );
                break;
        }
        return $this;
    }
    
    public function setLimit( int $limit, int $offset = 0 ): CriteriaBuilder
    {
        $this->selectQuery->setLimit( $limit, $offset );
        return $this;
    }

    public function orderBy( string $fieldname, string $order ): CriteriaBuilder
    {
        $this->selectQuery->setOrderby( [ self::getField( $fieldname ) ], [ $order ] );
        return $this;
    }

    public function count(): CriteriaBuilder
    {
        $this->selectQuery->setSelect( new Aggregate( 'count' ) );
        return $this;
    }

    #region INSERT SECTION

    public function insert( string $tablename = null ): CriteriaBuilder
    {
        $this->insertQuery = new InsertQuery( ( is_null( $tablename ) ) ? $this->tablename : $tablename );
        $this->setQueryMode( self::INSERT );
        return $this;
    }

    public function values( array $values ): CriteriaBuilder
    {
        switch ( $this->query ) {
            case self::UPDATE:
                $this->updateQuery->setValues( $values );
                break;
            default:
                $this->insertQuery->setValues( $values );
                break;
        }
        return $this;
    }
    
    #endregion INSERT SECTION

    #region UPDATE SECTION

    public function update( string $tablename = null ): CriteriaBuilder
    {
        $this->updateQuery = new UpdateQuery( ( is_null( $tablename ) ) ? $this->tablename : $tablename );
        $this->setQueryMode( self::UPDATE );
        return $this;
    }
    
    #endregion UPDATE SECTION
    
    #region DELETE SECTION

    public function delete( string $tablename = null ): CriteriaBuilder
    {
        $this->deleteQuery = new DeleteQuery( ( is_null( $tablename ) ) ? $this->tablename : $tablename );
        $this->setQueryMode( self::DELETE );
        return $this;
    }
    
    #endregion DELETE SECTION
    
    // STATICS METHODS

    public static function equals( string $fieldname, $value ): Condition
    {
        return new ConditionImpl( self::EQUALS, self::getField( $fieldname ), $value );
    }

    public static function in( string $fieldname, array $values ): Condition
    {
        return new ConditionImpl( self::IN, self::getField( $fieldname ), $values );
    }

    // PRIVATE METHODS
    
    private static function getField( string $fieldname ): Field
    {
        return new FieldImpl( $fieldname );
    }

    private function getAndOperator( array $conditions ): AndOperator
    {
        return new AndOperator( $conditions );
    }

    private function setQueryMode( string $mode ): void
    {
        switch ( $mode ) {
            case self::UPDATE:
                $this->clearSelect();
                $this->clearInsert();
                $this->clearDelete();
                break;
            case self::DELETE:
                $this->clearSelect();
                $this->clearInsert();
                $this->clearUpdate();
                break;
            case self::INSERT:
                $this->clearSelect();
                $this->clearUpdate();
                $this->clearDelete();
                break;
            default:
                $this->clearInsert();
                $this->clearUpdate();
                $this->clearDelete();
                break;
        }
        $this->query = $mode;
    }
    
    private function clearUpdate(): void
    {
        $this->updateQuery = null;
    }

    private function clearSelect(): void
    {
        $this->selectQuery = null;
    }

    private function clearInsert(): void
    {
        $this->insertQuery = null;
    }

    private function clearDelete(): void
    {
        $this->deleteQuery = null;
    }
    
}

?>