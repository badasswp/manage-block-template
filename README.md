# manage-block-template

A simple plugin to manage block templates easily.

---

<img width="352" height="196" alt="screenshot-1" src="https://github.com/user-attachments/assets/9505d9e8-575c-4681-834e-0882cad472ea" />

## Why Manage Block Template?

Creating and managing block templates for different post types within your WP website doesn't have to be rocket science! With this plugin, you can easily create multiple block templates of your choice and assign them to specific post types within your website. It's fast and super easy to use.

This plugin is perfect for website owners who run blogs, news or websites with tons of post types! Now, you have one less to worry about...

https://github.com/user-attachments/assets/4b509263-b296-447d-a700-5d1a12aafb10

### Hooks

#### `manage_block_template_admin_fields`

This custom hook (filter) provides a way to filter the admin fields presented on the options page of the plugin.

```php
add_filter( 'manage_block_template_admin_fields', [ $this, 'custom_admin_fields' ] );

public function custom_admin_fields( $fields ): array {
    $fields[] = [
        'name'    => 'name_of_your_control',
        'label'   => __( 'Control Label', 'your-text-domain' ),
        'cb'      => [ $this, 'name_of_your_control_callback' ],
        'page'    => 'manage-block-template',
        'section' => 'manage-block-template-section',
    ];

    return $fields;
}
```

**Parameters**

- fields _`{array}`_ By default this will be an array containing key, value options for the control.
<br/>

#### `manage_block_template_blocks`

This custom hook (filter) provides a way to filter the blocks passed to a specific post type's template.

```php
add_filter( 'manage_block_template_blocks', [ $this, 'custom_blocks' ], 10, 2 );

public function custom_blocks( $blocks, $post_type ): array {
    if ( 'your_custom_post_type' === $post_type ) {
        $blocks[] = [
            'your-custom-block-name',
            [
                'placeholder' => 'your-placeholder',
            ],
        ];
    }

    return $blocks;
}
```

**Parameters**

- blocks _`{array}`_ By default this will be an index array containing arrays with block names and attributes.
- post_type _`{string}`_ By default this will be the current post type.
<br/>

#### `manage_block_template_post_types`

This custom hook (filter) provides a way to filter the post types available for block templates.

```php
add_filter( 'manage_block_template_post_types', [ $this, 'custom_post_types' ] );

public function custom_post_types( $post_types ): array {
    $post_types[] = esc_html(
        'your_custom_post_type'
    );

    return $post_types;
}
```

**Parameters**

- post_types _`{array}`_ By default this will be an index array containing post type names.
<br/>

## Contribute

Contributions are __welcome__ and will be fully __credited__. To contribute, please fork this repo and raise a PR (Pull Request) against the `master` branch.

### Pre-requisites

You should have the following tools before proceeding to the next steps:

- Composer
- Yarn
- Docker

To enable you start development, please run:

```bash
yarn start
```

This should spin up a local WP env instance for you to work with at:

```bash
http://manage-block-template.localhost:8496
```

You should now have a functioning local WP env to work with. To login to the `wp-admin` backend, please use `admin` for username & `password` for password.

__Awesome!__ - Thanks for being interested in contributing your time and code to this project!
