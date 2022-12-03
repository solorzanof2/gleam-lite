<?php

namespace emerus\data;

/**
 * @Entity
 * @Table(name="databasechangelog")
 */
class Changelog extends Entity
{

	/**
	 * @Column(name="author", type="string")
	 */
	public $author;

	/**
	 * @Column(name="title", type="string")
	 */
	public $title;

	/**
	 * @Column(name="route", type="string")
	 */
	public $route;

	/**
	 * @Column(name="dateexecuted", type="string")
	 */
	public $dateexecuted;

	/**
	 * @Column(name="signature", type="string")
	 */
	public $signature;
}
