# Lexical analysis (tokenizer)

```php
$source = "var {{ name }}: {{ type }};";
$lexer  = new \Bavix\Lexer\Lexer();
$tokens = $lexer->tokens($source);
```

Results:
```
array(4) {
  [1] =>
  array(0) {
  }
  [2] =>
  array(0) {
  }
  [4] =>
  array(2) {
    '{{ name }}' =>
    array(7) {
      'type' =>
      int(4)
      'print' =>
      bool(true)
      'escape' =>
      bool(true)
      'name' =>
      string(10) "T_VARIABLE"
      'code' =>
      string(10) "{{ name }}"
      'fragment' =>
      string(4) "name"
      'tokens' =>
      array(1) {
        ...
      }
    }
    '{{ type }}' =>
    array(7) {
      'type' =>
      int(4)
      'print' =>
      bool(true)
      'escape' =>
      bool(true)
      'name' =>
      string(10) "T_VARIABLE"
      'code' =>
      string(10) "{{ type }}"
      'fragment' =>
      string(4) "type"
      'tokens' =>
      array(1) {
        ...
      }
    }
  }
  [8] =>
  array(0) {
  }
}
```

---
Supported by

[![Supported by JetBrains](https://cdn.rawgit.com/bavix/development-through/46475b4b/jetbrains.svg)](https://www.jetbrains.com/)

