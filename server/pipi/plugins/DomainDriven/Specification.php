<?php



///////////////////////////////////////////////////////
// Specification - from code.google.com/p/getspecific
// by bytebrite@gmail.com
//////////////////////////////////////////////////////
/**
 * Specification
 * 
 */
class Specification {
    /**
     * Tests an object/array against this specification
     * 
     * This is a template method. Subclasses must override the _isSatisfiedBy()
     * method instead of this one when implementing their logic.
     *
     * @param array $object an array or an object that implements the ArrayAccess interface
     * @return bool whether or not the given object satisfies the specification 
     */
    final function isSatisfiedBy($object) {
        if (!$this->isArray($object)) {
            return false;
        }
        return $this->_isSatisfiedBy($object);
    }
    /**
     * Default implementation of _isSatisfiedBy()
     *
     * @param array $object an array or an object that implements the ArrayAccess interface
     * @return bool true
     */
    protected function _isSatisfiedBy($object) {
        return true;
    }
    /**
     * Tests a variable for array access
     *
     * @param mixed $object
     * @return bool whether or not the variable is an array or implements the ArrayAccess interface
     */
    private function isArray($object) {
        return is_array($object) || $object instanceof ArrayAccess;
    }
    /**
     * Returns child Specifications
     *
     * @return null leaf Specification has no children 
     */
    function getChildren() {
        return null;
    }
    /**
     * Accepts visitors
     *
     * @param object $visitor
     * @param int $depth current depth in the composite structure
     */
    function accept($visitor, $depth = 0) {
        $visitor->visit($this, $depth);
    }
}

/**
 * Composite Specification
 *
 */
class CompositeSpecification extends Specification {
    /**
     * Holds child Specifications
     *
     * @var array
     */
    protected $specs = array();
    /**
     * Constructor
     *
     * @param array $specs
     */
    function __construct($specs) {
        $this->specs = $specs;
    }
    /**
     * Returns child Specifications
     *
     * @return array child Specifications
     */
    function getChildren() {
        return $this->specs;
    }
    /**
     * Accepts visitors
     *
     * @param object $visitor
     * @param int $depth current depth in the composite structure
     */
    function accept($visitor, $depth = 0) {
        parent::accept($visitor, $depth);
        foreach ($this->specs as $spec) {
            $visitor->visit($spec, $depth + 1);
        }
    }
}

/**
 * LogicalAnd is satisified iff all 
 * child Specifications are satisfied
 *
 */
class LogicalAnd extends CompositeSpecification {
    protected function _isSatisfiedBy($object) {
        foreach ($this->specs as $spec) {
            if (!$spec->isSatisfiedBy($object)) {
                return false;
            }
        }
        return true;
    }
}

/**
 * LogicalOr is satisified if at least one 
 * child Specification is satisfied
 *
 */
class LogicalOr extends CompositeSpecification {
    protected function _isSatisfiedBy($object) {
        foreach ($this->specs as $spec) {
            if ($spec->isSatisfiedBy($object)) {
                return true;
            }
        }
        return false;
    }
}

/**
 * LogicalXor is satisified iff one 
 * child Specification is satisfied
 *
 */
class LogicalXor extends CompositeSpecification {
    protected function _isSatisfiedBy($object) {
        $alreadySatisfied = false;
        foreach ($this->specs as $spec) {
            if ($spec->isSatisfiedBy($object)) {
                if ($alreadySatisfied) {
                    return false;
                }
                $alreadySatisfied = true;
            }
        }
        return $alreadySatisfied;
    }
}

/**
 * LogicalNot is satisified iff zero 
 * child Specifications are satisfied
 *
 */
class LogicalNot extends CompositeSpecification {
    protected function _isSatisfiedBy($object) {
        foreach ($this->specs as $spec) {
            if ($spec->isSatisfiedBy($object)) {
                return false;
            }
        }
        return true;
    }
}


/**
 * ValueBoundSpecification
 *  
 */
class ValueBoundSpecification extends Specification {
    public $key;
    public $val;
    function __construct($key, $val) {
        $this->key = $key;
        $this->val = $val;
    }
}

/**
 * LessThan Comparison
 *
 */
class LessThan extends ValueBoundSpecification {
    function _isSatisfiedBy($object) {
        return $object[$this->key] < $this->val;
    }
}

/**
 * GreaterThan Comparison
 *
 */
class GreaterThan extends ValueBoundSpecification {
    function _isSatisfiedBy($object) {
        return $object[$this->key] > $this->val;
    }
}

/**
 * LessThanOrEqualTo Comparison
 *
 */
class LessThanOrEqualTo extends ValueBoundSpecification {
    function _isSatisfiedBy($object) {
        return $object[$this->key] <= $this->val;
    }
}

/**
 * GreaterThanOrEqualTo Comparison
 *
 */
class GreaterThanOrEqualTo extends ValueBoundSpecification {
    function _isSatisfiedBy($object) {
        return $object[$this->key] >= $this->val;
    }
}

/**
 * EqualTo Comparison
 *
 */
class EqualTo extends ValueBoundSpecification {
    function _isSatisfiedBy($object) {
        return $object[$this->key] == $this->val;
    }
}

/*
 * TODO: add MatchSpecification
 *
 * MatchSpecification will check a given field for a specific pattern,
 * e.g., numeric, alphanumeric, email, etc. 
 * 
 */

/**
 * SpecificationVisitor separates behavioral aspects 
 * from the composite structure of Specifications.
 * 
 */
abstract class SpecificationVisitor {
    abstract function visit($spec, $depth);
    function visitLogicalAnd($spec, $depth) {
        $this->visit($spec, $depth);
    }
    function visitLogicalOr($spec, $depth) {
        $this->visit($spec, $depth);
    }
    function visitLogicalXor($spec, $depth) {
        $this->visit($spec, $depth);
    }
    function visitLogicalNot($spec, $depth) {
        $this->visit($spec, $depth);
    }
    function visitComparison($spec, $depth) {
        $this->visit($spec, $depth);
    }
}

/**
 * TODO: MysqlCriteriaFromSpecBuilder, SpecScriptFromSpecBuilder,
 *       PhpArrayFromSpecBuilder, SpecBasedValidator, etc.
 * 
 */

/**
 * Collection is essentially a queryable array,
 * i.e., Specifications may be used as indexes 
 *
 */
class Collection implements ArrayAccess, Iterator {
    private $i = 0;
    protected $data = array();
    function offsetExists($key) {
    }
    function offsetGet($key) {
        if ($key instanceof Specification) {
            return new CollectionProxy($this, $key);
        }
        return $this->data[$key];
    }
    function offsetSet($key, $val) {
        if (!is_array($key)) {
            if (is_null($key)) {
                $this->data[] = $val;
                return;
            }
            $this->data[$key] = $val;
            return;
        }
        if (empty($this->data)) {
            $this->data[] = array();
        }
        foreach ($this->data as $i => $row) {
            foreach ($key as $_key) {
                $this->data[$i][$_key] = $val;
            }
        }
    }
    function offsetUnset($key) {
    }
    function current() {
        return current($this->data);
    }
    function key() {
        return key($this->data);
    }
    function next() {
        ++$this->i;
        return next($this->data);
    }
    function rewind() {
        $this->i = 0;
        reset($this->data);
    }
    function valid() {
        return $this->i < count($this->data);
    }
    function length() {
        return count($this->data);
    }
    function getIterator() {
        return $this;
    }
}

class CollectionProxy extends Collection {
    protected $collection;
    protected $spec;
    function __construct($collection, Specification $spec) {
        $this->collection = $collection;
        $this->spec = $spec;
    }
    function offsetSet($key, $val) {
        if ($this->spec->isSatisfiedBy($val)) {
            $this->collection->offsetSet($key, $val);
        }
    }
}

?>