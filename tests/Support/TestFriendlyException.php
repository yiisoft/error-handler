<?php

declare(strict_types=1);

namespace Yiisoft\ErrorHandler\Tests\Support;

use Exception;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

/**
 * For tune appearance.
 */
final class TestFriendlyException extends Exception implements FriendlyExceptionInterface
{
    public function getName(): string
    {
        return 'Test Exception';
    }

    public function getSolution(): ?string
    {
        return <<<SOLUTION
        # Heading 1

        Yii is a generic Web programming framework. It can be used for developing all kinds of Web applications using
        PHP. Because of its component-based architecture and sophisticated caching support, it is especially suitable
        for developing large-scale applications such as portals, forums, content management systems (CMS), e-commerce
        projects, RESTful Web services, and so on.

        Using Yii requires basic knowledge of object-oriented programming (OOP), as Yii is a pure OOP-based framework.
        Yii3 also makes use of the latest features of PHP, such as type declarations and generators. Understanding these
        concepts will help you more easily pick up Yii3.

        ## Heading 2

        Using Yii requires basic knowledge of object-oriented programming (OOP), as Yii is a pure OOP-based framework.
        Yii3 also makes use of the latest features of PHP, such as type declarations and generators. Understanding these
        concepts will help you more easily pick up Yii3.

        ### Heading 3

        Using Yii requires basic knowledge of object-oriented programming (OOP), as Yii is a pure OOP-based framework.
        Yii3 also makes use of the latest features of PHP, such as type declarations and generators. Understanding these
        concepts will help you more easily pick up Yii3.

        #### Heading 4

        Using Yii requires basic knowledge of object-oriented programming (OOP), as Yii is a pure OOP-based framework.
        Yii3 also makes use of the latest features of PHP, such as type declarations and generators. Understanding these
        concepts will help you more easily pick up Yii3.

        ##### Heading 5

        Using Yii requires basic knowledge of object-oriented programming (OOP), as Yii is a pure OOP-based framework.
        Yii3 also makes use of the latest features of PHP, such as type declarations and generators. Understanding these
        concepts will help you more easily pick up Yii3.

        ###### Heading 6

        Using Yii requires basic knowledge of object-oriented programming (OOP), as Yii is a pure OOP-based framework.
        Yii3 also makes use of the latest features of PHP, such as type declarations and generators. Understanding these
        concepts will help you more easily pick up Yii3.

        Alternative Heading 1
        =====================

        Using Yii requires basic knowledge of object-oriented programming (OOP), as Yii is a pure OOP-based framework.
        Yii3 also makes use of the latest features of PHP, such as type declarations and generators. Understanding these
        concepts will help you more easily pick up Yii3.

        Alternative Heading 2
        ---------------------

        Using Yii requires basic knowledge of object-oriented programming (OOP), as Yii is a pure OOP-based framework.
        Yii3 also makes use of the latest features of PHP, such as type declarations and generators. Understanding these
        concepts will help you more easily pick up Yii3.

        ### Blockquote

        Paragraph before blockuite.

        > Using Yii requires basic knowledge of object-oriented programming (OOP), as Yii is a pure OOP-based framework.
        > Yii3 also makes use of the latest features of PHP, such as type declarations and generators. Understanding
        > these concepts will help you more easily pick up Yii3.

        Paragraph after blockuite.

        ### Blockquote nested

        > ## This is a header.
        >
        > 1.   This is the first list item.
        > 2.   This is the second list item.
        >
        > Here's some example code:
        >
        >     return shell_exec("echo \$input | \$markdown_script");
        >
        > > quote here

        ### Preformatted block

        Preformatted blocks are useful for ASCII art:

        <pre>
                     ,-.
            ,     ,-.   ,-.
           / \   (   )-(   )
           \ |  ,.>-(   )-<
            \|,' (   )-(   )
             Y ___`-'   `-'
             |/__/   `-'
             |
             |
             |    -hrr-
          ___|_____________
        </pre>

        ### Lists

        Text before list:

         * item 1,
         * item 2,
         * item 3.

        Text after list.

        - test
        - test
           - test
           - test
        - test

        Ordered list:

        1. Item A
        2. Item B
        3. Item C

        ### Table

        | Name | Description |
        | ---- | ----------- |
        | Help | Display the help window |
        | Close | Closes a window |

        ### Line

        Paragraph before line.

        ---

        Paragraph after line.

        ### Link

        Example of link: [yiiframework.com](https://www.yiiframework.com/).

        ### Code

        This is `inline code` into paragpraph.

        This is PHP code block:

        ```php
        \$html = [];
        foreach (\$flashes as \$type => \$data) {
            foreach (\$data as \$message) {
                \$html[] = Alert::widget()
                    ->options(['class' => "alert-{\$type} shadow"])
                    ->body(\$message['body'])
                ;
            }
        }
        ```

        This is HTML code block:

        ```html
        <html>
        <body>
            <p>This text is normal.</p>
            <p><b>This text is bold.</b></p>
        </body>
        </html>
        ```

        This is default code block:

        ```
        Default code
        ```

        This is very long code block:

        ```
        Using Yii requires basic knowledge of object-oriented programming (OOP), as Yii is a pure OOP-based framework. Yii3 also makes use of the latest features of PHP, such as type declarations and generators. Understanding these concepts will help you more easily pick up Yii3.
        ```

        ### Test XSS

        <script>alert(45);</script>
        SOLUTION;
    }
}
