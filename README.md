# EPT
Handlungsanweisung Vorbereitung,Durchführung,Nachbereitung

## Adding a new test

1. **Register the test**
   Add a new entry to `database/seeders/TestsTableSeeder.php` with the test
   `code`, human readable `name` and its `duration` in minutes:

   ```php
   ['code' => 'ABC', 'name' => 'Example Test', 'duration' => 30],
   ```

   Run the seeder to persist it:

   ```bash
   php artisan db:seed --class=TestsTableSeeder
   ```

2. **Create the Vue page**
   Place the new component in `resources/js/pages/ABC.vue` (replace `ABC` with
   the test code). The component should emit `complete` once the participant has
   finished, allowing the exam room to advance to the next step:

   ```ts
   // inside ABC.vue
   emit('complete')
   ```

3. **Submit the result via the shared API**
   Before emitting `complete`, use the shared result‑submission API to send the
   payload (answers, timings, scores, …) to the server so that all tests store
   results consistently:

   ```ts
   import { submitTestResult } from '@/lib/results' // shared helper

   async function finishTest(payload: any) {
     await submitTestResult('ABC', payload)
     emit('complete')
   }
   ```

4. *(Optional)* **Expose a route**
   If the test should be directly accessible, add a route in `routes/web.php`:

   ```php
   Route::get('abc', fn () => Inertia::render('ABC'))->name('abc');
   ```

Following these steps ensures new tests are registered in the database, have a
corresponding Vue page and submit their results through the shared API.
