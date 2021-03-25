# PHP Pdf Gen
PDF generator from php array templates

## Table of Contents
- [Template](#template)
    - [document](#document)
    - [header](#header)
    - [body](#body)
    - [footer](#footer)
- [Objects](#objects)
    - [Rect](#rect)
    - [Image](#image)
    - [Text](#text)
    - [Table](#table)
    
### document
Defines the global properties of the PDF document.

| Attribute | Value |
|-----------|-------|
|`orientation`| Page orientation. Possible values are `portrait`, `landscape` and '' (empty string) for automatic orientation. Optional, `portrait` if not specified|
|`unit`|User measure unit. Possible values are:<ul><li>pt: point</li><li>mm: millimeter (default)</li><li>cm: centimeter</li><li>in: inch</li></ul>A point equals 1/72 of inch, that is to say about 0.35 mm (an inch being 2.54 cm). This is a very common unit in typography; font sizes are expressed in that unit.|
|`page-format`| One of the string values specified at [`TCPDF_STATIC::$page_formats`](https://github.com/tecnickcom/TCPDF/blob/main/include/tcpdf_static.php#L2129). Optional, `A4` if not specified|
|`margin`| Array of margins, left, top, right and bottom, for example `[ 10, 10, 10, 10 ]`. You can use single value to make all margins equals.|
| `font-family` | Family font. It can be either a name defined by AddFont() or one of the standard Type1 families (case insensitive):<ul><li>times (Times-Roman)</li><li>timesb (Times-Bold)</li><li>timesi (Times-Italic)</li><li>timesbi (Times-BoldItalic)</li><li>helvetica (Helvetica)</li><li>helveticab (Helvetica-Bold)</li><li>helveticai (Helvetica-Oblique)</li><li>helveticabi (Helvetica-BoldOblique)</li><li>courier (Courier)</li><li>courierb (Courier-Bold)</li><li>courieri (Courier-Oblique)</li><li>courierbi (Courier-BoldOblique)</li><li>symbol (Symbol)</li><li>zapfdingbats (ZapfDingbats)</li></ul> It is also possible to pass an empty string. In that case, the current family is retained.. |
| `font-size` | Font size in points.<br/>The default value is the current size. If no size has been specified since the beginning of the document, the value taken is 12. |
| `font-style` | Font style. Possible values are (case insensitive):<ul><li>empty string: regular</li><li>B: bold</li><li>I: italic</li><li>U: underline</li><li>D: line through</li><li>O: overline</li></ul> or any combination.<br />The default value is regular.<br />Bold and italic styles do not apply to Symbol and ZapfDingbats basic fonts or other fonts when not defined. |
| `padding` | Global internal cell padding. Number or array of cell paddings for left, top, right and bottom, for example `2` or `[ 2, 2, 2, 2 ]`|

### header
Defines the `header` objects

| Attribute | Value |
|-----------|-------|
|`objects`| Array of [Objects](#objects)|
|`margin`| Margin between header and body (at the header bottom)|

### body
Defines the `body` objects

| Attribute | Value |
|-----------|-------|
|`objects`| Array of [Objects](#objects)|

### footer
Defines the `footer` objects

| Attribute | Value |
|-----------|-------|
|`objects`| Array of [Objects](#objects)|

### Objects

All objets needs an attribute named `type` whose value will determine what object will render. Each object type needs different attributes, as shown below.

#### Rect

| Class               | Description                                                |
|---------------------|------------------------------------------------------------|
| `type` | `rect` |
| `left` | Abscissa of upper-left corner. `true` value indicates current abscissa. |
| `top` | Ordinate of upper-left corner. `true` value indicates current ordinate. |
| `width` | Width. Don't define if you want to use `right`. |
| `height` | Height. Don't define if you want to use `bottom`. |
| `right` | Abscissa of bottom-right corner. Don't define `width` to use this. |
| `bottom` | Ordinate of bottom-right corner. Don't define `height` to use this. |
| `border` | [CSS border property](https://developer.mozilla.org/en/docs/Web/CSS/border). |
| `border-width` | [CSS border-width property](https://developer.mozilla.org/en-US/docs/Web/CSS/border-width). |
| `border-style` | [CSS border-style property](https://developer.mozilla.org/en-US/docs/Web/CSS/border-style). |
| `border-color` | [CSS border-color property](https://developer.mozilla.org/en-US/docs/Web/CSS/border-color). |
| `border-radius` | The radius of the circle used to round off the corners of the rectangle. Only one value for all corners. |
| `background-color` | Fill the rectangle with specified color. |

#### Image

| Class               | Description                                                |
|---------------------|------------------------------------------------------------|
| `type` | `image` |
| `left` | Abscissa of upper-left corner. `true` value indicates current abscissa. |
| `top` | Ordinate of upper-left corner. `true` value indicates current ordinate. |
| `width` | Width. Don't define if you want to use `right`. |
| `height` | Height. Don't define if you want to use `bottom`. |
| `right` | Abscissa of bottom-right corner. Don't define `width` to use this. |
| `bottom` | Ordinate of bottom-right corner. Don't define `height` to use this. |
| `src` | Name of the file containing the image or a '@' character followed by the image data string. To link an image without embedding it on the document, set an asterisk character before the URL (i.e.: '*http://www.example.com/image.jpg'). |
| `center` | Set to `true` to center the image if smaller than `width` |

#### Text

| Class               | Description                                                |
|---------------------|------------------------------------------------------------|
| `type` | `text` |
| `left` | Abscissa of upper-left corner. `true` value indicates current abscissa. |
| `top` | Ordinate of upper-left corner. `true` value indicates current ordinate. |
| `width` | Width. Don't define if you want to use `right`. |
| `height` | Height. Don't define if you want to use `bottom`. |
| `right` | Abscissa of bottom-right corner. Don't define `width` to use this. |
| `bottom` | Ordinate of bottom-right corner. Don't define `height` to use this. |
| `color` | [CSS color property](https://developer.mozilla.org/en-US/docs/Web/CSS/color). |
| `border` | [CSS border property](https://developer.mozilla.org/en/docs/Web/CSS/border). |
| `border-width` | [CSS border-width property](https://developer.mozilla.org/en-US/docs/Web/CSS/border-width). |
| `border-style` | [CSS border-style property](https://developer.mozilla.org/en-US/docs/Web/CSS/border-style). |
| `border-color` | [CSS border-color property](https://developer.mozilla.org/en-US/docs/Web/CSS/border-color). |
| `align` | Allows to center or align the text. Possible values are:<ul><li>L or empty string: left align</li><li>C: center</li><li>R: right align</li><li>J: justification (default value when $ishtml=false)</li></ul> |
| `background-color` | Fill the rectangle with specified color. |
| `font-family` | Family font. It can be either a name defined by AddFont() or one of the standard Type1 families (case insensitive):<ul><li>times (Times-Roman)</li><li>timesb (Times-Bold)</li><li>timesi (Times-Italic)</li><li>timesbi (Times-BoldItalic)</li><li>helvetica (Helvetica)</li><li>helveticab (Helvetica-Bold)</li><li>helveticai (Helvetica-Oblique)</li><li>helveticabi (Helvetica-BoldOblique)</li><li>courier (Courier)</li><li>courierb (Courier-Bold)</li><li>courieri (Courier-Oblique)</li><li>courierbi (Courier-BoldOblique)</li><li>symbol (Symbol)</li><li>zapfdingbats (ZapfDingbats)</li></ul> It is also possible to pass an empty string. In that case, the current family is retained.. |
| `font-size` | Font size in points.<br/>The default value is the current size. If no size has been specified since the beginning of the document, the value taken is 12. |
| `font-style` | Font style. Possible values are (case insensitive):<ul><li>empty string: regular</li><li>B: bold</li><li>I: italic</li><li>U: underline</li><li>D: line through</li><li>O: overline</li></ul> or any combination.<br />The default value is regular.<br />Bold and italic styles do not apply to Symbol and ZapfDingbats basic fonts or other fonts when not defined. |
| `text` | Text to print. |
| `margin` | Margin at the top of the text block. |

#### Table

| Class               | Description                                                |
|---------------------|------------------------------------------------------------|
| `type` | `table` |
| `border-color` | Valid CSS color value. If set, table will hava all borders. |
| `font-family` | Family font. It can be either a name defined by AddFont() or one of the standard Type1 families (case insensitive):<ul><li>times (Times-Roman)</li><li>timesb (Times-Bold)</li><li>timesi (Times-Italic)</li><li>timesbi (Times-BoldItalic)</li><li>helvetica (Helvetica)</li><li>helveticab (Helvetica-Bold)</li><li>helveticai (Helvetica-Oblique)</li><li>helveticabi (Helvetica-BoldOblique)</li><li>courier (Courier)</li><li>courierb (Courier-Bold)</li><li>courieri (Courier-Oblique)</li><li>courierbi (Courier-BoldOblique)</li><li>symbol (Symbol)</li><li>zapfdingbats (ZapfDingbats)</li></ul> It is also possible to pass an empty string. In that case, the current family is retained.. |
| `font-size` | Font size in points.<br/>The default value is the current size. If no size has been specified since the beginning of the document, the value taken is 12. |
| `font-style` | Font style. Possible values are (case insensitive):<ul><li>empty string: regular</li><li>B: bold</li><li>I: italic</li><li>U: underline</li><li>D: line through</li><li>O: overline</li></ul> or any combination.<br />The default value is regular.<br />Bold and italic styles do not apply to Symbol and ZapfDingbats basic fonts or other fonts when not defined. |
| `margin` | Margin at the top of the table. |
| `padding` | internal cell padding. Number oy array of cell paddings for left, top, right and bottom, for example `2` or `[ 2, 2, 2, 2 ]`|
| `columns` | Array of columns.<br />Each column can have the following properties:<ul><li><b>width</b>: Column width</li><li><b>text</b>: text to display in the column header. Header won't be displayed if there isn't any header text in any column.</li><li><b>align</b>: text alignment in column. Possible values are:<ul><li>L or empty string: left align</li><li>C: center</li><li>R: right align</li><li>J: justification</li></ul></li></ul>|
| `rows` | Data to display. Array of rows or object wich implements <code>Iterator</code>. Each row needs to be an array with same number of elements as columns. |
| `` | |
| `` | |
| `` | |
| `` | |
| `` | |

