# AvantImport

AvantImport is a derivation of [CSV Import+](https://github.com/Daniel-KM/Omeka-plugin-CsvImportPlus).
It was created for use with the Digital Archive. Compared to CSV Import++, AvantImport has a much simpler
user interface because it presents far fewer options, only those useful for import into the Digital Archive.
It also has more robust detection and handling of invalid input, and only allows import of CSV files that
are in UTF-8 format. It requires UTF-8 to ensure that CSV text with non-ASCII characters (such as letters
with accent marks) can be imported without triggering a MySQL error.

## Warning

Use this software at your own risk.

##  License

This plugin is published under [GNU/GPL].

This program is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation; either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
details.

You should have received a copy of the GNU General Public License along with
this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

Copyright
---------

* Modifed by [gsoules](https://github.com/gsoules) based on
 [CSV Import+](https://github.com/Daniel-KM/Omeka-plugin-CsvImportPlus)
 by [Daniel Berthereau](https://github.com/Daniel-KM) 

* Copyright Center for History and New Media, 2008-2016
* Copyright Shawn Averkamp, 2012
* Copyright Matti Lassila, 2016
* Copyright Daniel Berthereau, 2012-2017
* Copyright George Soules, 2020
* See [LICENSE](https://github.com/gsoules/AvantImport/blob/master/LICENSE) for more information.
