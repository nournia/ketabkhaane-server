<?php 

class Admin_Controller extends Base_Controller {
	
	public function auth()
	{
		$key = Input::get('key');
		if ($key) {
			$row = DB::query('select upassword from users where id = 101139');
			if (count($row) > 0)
				return sha1($key) === $row[0]->upassword;
			return true;
		}
		return false;
	}

	public function action_manage()
	{
		if ($this->auth()) {
			// library slug update
			if (Input::get('library_id') && Input::get('slug'))
				DB::query('update libraries set slug=? where id = ?', array(Input::get('slug'), Input::get('library_id')));

			// library list
			$libraries = DB::query('select libraries.id, libraries.slug, if(libraries.title != "", libraries.title, concat("توسط ", users.firstname, " ", users.lastname)) as title from libraries
					inner join (select library_id, min(user_id) as master_id from permissions where permission = "master" group by library_id) as _t on libraries.id = _t.library_id
					inner join users on master_id = users.id  order by libraries.slug desc');
			return View::make('admin.libraries', array('libraries' => $libraries));		
		}
	
		return View::make('admin.index');
	}

	public function action_dump()
	{
		if ($this->auth()) {
			$filesDir = path('storage') .'files/';
			$archive = 'files_'. date('Y-m-d') .'.zip';
			$zip = new ZipArchive;
			$zip->open($filesDir.$archive, ZipArchive::CREATE);
			if (false === ($dir = opendir($filesDir)))
				echo "Can't read $filesDir";
			else
				while (false !== ($file = readdir($dir)))
					if ($file != '.' && $file != '..' && File::extension($file) != 'zip')
						$zip->addFile($filesDir.$file, $file);
			$zip->close();

			return Redirect::to('/files/'. $archive);
		}

		return View::make('admin.index');
	}
}
