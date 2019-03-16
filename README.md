# ActiveRecord

- set()
- save()
- read()
- delete()
- query()
- escape()
- fields()
- table()
- id()

static:

- config()
- objects()

## set()

	$note = new Note();
	$note->set($data); // $data -- Array or Object

## save()

	$note->save();

## read()

	$note->read(1);

## delete()

	$note->delete();

## query();

	$result = $this->query(...);

## escape()

	$text = $this->escape($text);

## fields()

	$fields = $this->fields();

## table()

	$table = $this->table();

## id()

	$product = new Product();
	$product->set(...);
	$product->save();
	$id = $product->id();

---------------------------------------------------------------------------------

## config()

	ActiveRecord::config('host', 'user', 'password', 'database');

## objects()

	$notes = Note::objects('SELECT * FROM Note ORDER BY id DESC');
	foreach ($notes as $note) {
		...
	}

