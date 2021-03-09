Jarvis
=
A small interface system for common AWS configs

Install
-
1. Run `composer install`
2. To use the RDS / MySQL functions, create a file in `config` directory called `rds.json` with the following format
    ```json
    {
      "rds" : {
        "username": "root",
        "password": "root"
      }
    }
    ```
3. Set up [AWS credentials](https://docs.aws.amazon.com/cli/latest/userguide/cli-configure-files.html) file
4. Commands can then be run using `php console create:s3:bucket`
5. If you want to you can run `sudo ln -s $(pwd)/console /usr/local/bin/jarvis` which will allow you to simply run the `jarvis` command from anywhere on your system