## Laravel Scaffolding

Scaffold an application.

This packages is still under development. So use on own risk.

Create entity models in Yaml format, let me give you an example

```yaml
name: User

table: users

attributes:
  - name: email
    type: string
    fillable: true
    rules: [required, email]
  - name: password
    type: string
    fillable: true
    rules: ['min:10']
  - name: firstname
    type: string
    fillable: true
    rules: [required, 'min:2']
  - name: lastname
    type: string
    fillable: true
    rules: [required, 'min:2']

settings:
  destroyable: true
  editable: true
  increments: true
  timestamps: true
  softdeletes: true
  auth: true

relations:
  has_many:
    - entity: Task
    - entity: Something
      key: some_id
```

Put them in a folder called `entities` at the root of your application.

Now you have two artisan commands to use

 - `artisan scaffold` to scaffold the application
 - `artisan scaffold:clear` to remote the scaffolding