<?php

function getResults($results) {
	$data = array();
	foreach($results as $row)
		$data[] = array_values(get_object_vars($row));
	return $data;
}

function response($data) {
	$headers = array('Access-Control-Allow-Origin' => '*', 'Cache-Control' => 'max-age='. CACHE_MINUTES*60);
	return Response::json($data, 200, $headers, JSON_NUMERIC_CHECK); // JSON_UNESCAPED_UNICODE
}

class Data_Controller extends Base_Controller {

	public function action_object_list($library_id)
	{
		return Cache::remember('object_list_'.$library_id, function() use($library_id) {
			$objects = getResults(DB::query('
				select objects.title, authors.title as author, publications.title as publication, objects.type_id, belongs.branch_id, belongs.cnt - ifnull(_borrowed.cnt, 0) > 0 as cnt from objects
				inner join belongs on objects.id = belongs.object_id
				left join authors on objects.author_id = authors.id
				left join publications on objects.publication_id = publications.id
				left join _borrowed on objects.id = _borrowed.object_id and belongs.library_id = _borrowed.library_id
				where belongs.library_id = ?
			', array($library_id)));

			$branches = getResults(DB::query('
				select branches.id, if(branches.title != "", concat(roots.title , " - ", branches.title), roots.title) as title from branches
				inner join (select distinct branch_id from belongs where library_id = ?) as _belongs on branches.id = _belongs.branch_id
				inner join roots on branches.root_id = roots.id
				order by branches.id
			', array($library_id)));

			return response(array('branches' => $branches, 'objects' => $objects));
		}, CACHE_MINUTES);
	}

	public function action_branch_stats($library_id)
	{
		return Cache::remember('branch_stats_'.$library_id, function() use($library_id) {
			$branches = getResults(DB::query('
				select branches.id, if(branches.title != "", concat(roots.title , " - ", branches.title), roots.title) as title from branches
				inner join (select distinct branch_id from belongs where library_id = ?) as _belongs on branches.id = _belongs.branch_id
				inner join roots on branches.root_id = roots.id
				order by branches.id
			', array($library_id)));

			$objects = array();
			foreach (DB::query('select object_id, branch_id from belongs where library_id = ?', array($library_id)) as $object)
				$objects[$object->object_id] = $object->branch_id;

			$dates = array();
			foreach (DB::query('select object_id, date(delivered_at) as delivered from borrows where library_id = ?', array($library_id)) as $item) {
				$delivered = $item->delivered;
				if (empty($dates[$delivered]))
					$dates[$delivered] = array();

				$dates[$delivered][] = $objects[$item->object_id];
			}

			return response(array('dates' => $dates, 'branches' => $branches));
		}, CACHE_MINUTES);
	}

	public function action_object_search($libraries, $query)
	{
		$objects = getResults(DB::query('
			select objects.title, authors.title as author, publications.title as publication, objects.type_id, libraries.title as library, belongs.cnt - ifnull(_borrowed.cnt, 0) > 0 as cnt from objects
			inner join belongs on objects.id = belongs.object_id
			left join authors on objects.author_id = authors.id
			left join publications on objects.publication_id = publications.id
			left join _borrowed on objects.id = _borrowed.object_id and belongs.library_id = _borrowed.library_id
			left join libraries on belongs.library_id = libraries.id
			where belongs.library_id in ('. str_replace('-', ',', $libraries) .') and (objects.title like "%'. urldecode($query) .'%") limit 20
		', array()));

		return response(array('objects' => $objects));
	}

	public function action_match_list($library_id)
	{
		return Cache::remember('match_list_'.$library_id, function() use($library_id) {
			$matches = getResults(DB::query('
				select matches.id, matches.title, ageclasses.title as ageclass, ifnull(types.title, categories.title) as kind, if(matches.category_id is null, trim(left(matches.content, 5)), "-") as answers_ratio from matches
				left join objects on matches.object_id = objects.id
				left join types on objects.type_id = types.id
				left join ageclasses on matches.ageclass = ageclasses.id
				left join categories on matches.category_id = categories.id
				where matches.id div 100000 != ?
			', array($library_id)));

			return response(array('matches' => $matches, 'operation' => 'list'));
		}, CACHE_MINUTES);
	}

	public function action_match_items($items) 
	{
		$objects = array(); $authors = array(); $publications = array(); $contents = array(); $files = array();

		// matches
		$items = str_replace('-', ',', $items);
		$matches = getResults(DB::query("select * from matches where id in ($items)"));
		foreach ($matches as $match) {
			if ($match[4]) $objects[] = $match[4];
			if ($match[6]) $contents[] = $match[6];
		}

		// files
		foreach ($contents as $content) {
			preg_match_all('/src="([^"]+)"/', $content, $cases);
			foreach($cases[1] as $case) {
				$filename = explode('.', $case);
				$files[] = $filename[0];
			}
		}
		if ($files)
			$files = getResults(DB::query('select * from files where id in ('. join(',', $files) .')'));

		// objects, authors and publications
		if ($objects) {
			$objects = getResults(DB::query('select * from objects where id in ('. join(',', $objects) .')'));
			foreach ($objects as $object) {
				if ($object[1]) $authors[] = $object[1];
				if ($object[2]) $publications[] = $object[2];
			}

			if ($authors)
				$authors = getResults(DB::query('select * from authors where id in ('. join(',', $authors) .')'));

			if ($publications)
				$publications = getResults(DB::query('select * from publications where id in ('. join(',', $publications) .')'));
		}

		return response(array('matches' => $matches, 'files' => $files, 'objects' => $objects, 'authors' => $authors, 'publications' => $publications, 'operation' => 'items'));
	}

	public function action_user_login($nationalId, $password)
	{
		$users = getResults(DB::query('select * from users where national_id = ? and upassword = ?', array($nationalId, $password)));
		if (count($users) == 1)
			return response(array('user' => $users, 'operation' => 'login'));

		return response(array('user' => array(), 'operation' => 'login'));
	}
}
