<?
use Nayuda\Core\Model;

class Model_Test extends Model{
   protected $_name = "test";	// table name
   protected $_pk = "id";	// primary key

   public function __construct($config = null){
       parent::__construct($config);
   }
}
?>
