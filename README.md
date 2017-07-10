# Dead code finder
Find dead code by comparing a used symbols list to the actual code.

## Usage
```bash
# List all symbols
./dcf symbols --directory ~/projects/myproject/app
# List all unused symbols
cat reports.txt | ./dcf unused:symbols --directory ~/projects/myproject/app
# List all unloaded files
cat reports.txt | ./dcf unused:files --directory ~/projects/myproject/app
```
