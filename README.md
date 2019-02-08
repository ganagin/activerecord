# ActiveRecord

- set()
- save()
- read()
- delete()

static:

- config()
- objects()
- query()
- escape()
- fields()
- table()
- primaryKey()

## set()

	$note = new Note();
	$note->set($data); // $data -- Array or Object

## save()

	$note->save();

## read()

	$note->read(1);

## delete()

	$note->delete();

---------------------------------------------------------------------------------

## config()

	ActiveRecord::config('host', 'user', 'password', 'database');

## objects()

	$notes = Note::objects('SELECT * FROM Note ORDER BY id DESC');
	foreach ($notes as $note) {
		...
	}

## query();

	if (Note::query('...')) {
		...
	}

## escape()

	$text = Note::escape($text);

## fields()

	$fields = Note::fields();

## table()

	$table = Note::table();

## primaryKey()

	$primaryKey = Note::primaryKey();

