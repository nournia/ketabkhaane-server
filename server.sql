CREATE TABLE libraries (
	id integer not null primary key auto_increment,
	title varchar(255) not null,
	description varchar(1000) null default null,
	synced_at timestamp null default null,
	license varchar(255) null default null,
	image varchar(50) null default null,
	version varchar(10) null,
	options text null
);

-- globals

CREATE TABLE ageclasses(
	id tinyint(4) not null,
	title varchar(255) not null,
	description varchar(255) null default null,
	beginage tinyint(4) not null,
	endage tinyint(4) not null,
	questions tinyint(4) not null
);
CREATE TABLE categories (
	id tinyint(4) not null,
	title varchar(255) not null
);
CREATE TABLE open_categories (
	id integer not null,
	title varchar(255) not null
);
CREATE TABLE types (
	id integer not null,
	title varchar(50) not null
);
CREATE TABLE accounts (
	id integer not null,
	title varchar(255) not null,
	bookfine integer not null, -- daily after one week
	cdfine integer not null -- daily
);

-- entities

CREATE TABLE users (
	library_id integer not null,
	id integer not null,
	national_id integer null default null,
	firstname varchar(255) not null,
	lastname varchar(255) not null,
	birth_date date null default null,
	address varchar(255) null default null,
	phone varchar(50) null default null,
	gender enum("male","female") not null,
	description varchar(255) null default null,
	email varchar(255) null default null,
	upassword char(40) null default null,
	label varchar(10) null default null,
	account smallint not null
);
CREATE TABLE authors (
	library_id integer not null,
	id integer not null,
	title varchar(255) not null
);
CREATE TABLE publications (
	library_id integer not null,
	id integer not null,
	title varchar(255) not null
);
CREATE TABLE objects (
	library_id integer not null,
	id integer not null,
	author_id integer null default null,
	publication_id integer null default null,
	type_id smallint not null,
	title varchar(255) not null,
	branch_id integer not null,
	label varchar(50) not null,
	cnt int not null default 0 -- count of object in this library
);
CREATE TABLE matches (
	library_id integer not null,
	id integer not null,
	designer_id integer null default null,
	title varchar(255) not null,
	ageclass tinyint(4) null default null,
	object_id integer null default null,
	category_id tinyint(4) null default null,
	content text null default null
);
CREATE TABLE questions (
	library_id integer not null,
	id integer not null,
	match_id integer not null,
	question varchar(1000) not null,
	answer varchar(1000) null default null
);
CREATE TABLE files (
	library_id integer not null,
	id integer not null,
	extension varchar(5) not null
);
CREATE TABLE roots (
	library_id integer not null,
	id integer not null,
	title varchar(255) not null,
	type_id smallint not null
);
CREATE TABLE branches (
	library_id integer not null,
	id integer not null,
	root_id integer not null,
	title varchar(255) not null,
	label varchar(10) null default null
);

-- reghaabats

CREATE TABLE answers (
	library_id integer not null,
	id integer not null,
	user_id integer not null,
	match_id integer not null,
	delivered_at datetime not null,
	received_at datetime null default null,
	corrected_at datetime null default null,
	rate float null default null
);
CREATE TABLE borrows (
	library_id integer not null,
	id integer not null,
	user_id integer not null,
	object_id integer not null,
	delivered_at datetime not null,
	received_at datetime null default null,
	renewed_at datetime null default null
);
CREATE TABLE open_scores (
	library_id integer not null,
	id integer not null,
	user_id integer not null,
	category_id tinyint(4) not null,
	title varchar(255) not null,
	score smallint(6) not null
);
CREATE TABLE permissions (
	library_id integer not null,
	id integer not null,
	user_id integer not null,
	permission enum("user", "operator", "designer", "manager", "master", "admin") not null,
	accept tinyint(1) not null default "0"
);
CREATE TABLE supports (
	library_id integer not null,
	id integer not null,
	match_id integer not null,
	corrector_id integer not null,
	current_state enum("active", "disabled", "imported") not null,
	score smallint
);
CREATE TABLE transactions (
	library_id integer not null,
	id integer not null,
	user_id integer not null,
	score smallint not null, -- match: +answer -payment, library: +receipt -fine +discount
	created_at datetime not null,
	description varchar(20) null -- fin: (fine of objects), dis (discount in fine), chg (money user charged to he's account), mid:match_id (score from match), pay (money payed to user for matches)
);

-- data

INSERT INTO ageclasses values (0, 'الف', 'آمادگی و اول دبستان', 6, 7, 4), (1, 'ب', 'دوم و سوم دبستان', 8, 9, 4), (2, 'ج', 'چهارم و پنجم دبستان', 10, 11, 5), (3, 'د', 'راهنمایی', 12, 14, 6), (4, 'ه', 'دبیرستان', 15, 18, 7);
INSERT INTO categories (id, title) values (0, 'نقاشی'), (1, 'رنگ‌آمیزی'), (2, 'تحقیق'), (3, 'آزمایش'), (4, 'کاردستی');
INSERT INTO open_categories (id, title) values (0, 'خلاصه‌نویسی'), (1, 'شعر'), (2, 'داستان');
INSERT INTO accounts (id, title, bookfine, cdfine) values (0, 'عادی', 50, 100), (1, 'ویژه', 25, 100);
INSERT INTO types (id, title) values (0, 'کتاب'), (1, 'چند رسانه‌ای');
