
# Bython

*php 7.4.0 or higher is required*

---

grammar:

    program: compoundStatement
    compoundStatement: statementList
    statementList: statement | statementList
    statement: assignment | input | print | if | empty
    if: AGAR condition COLON assignment
    condition: arg EQUAL arg
    assignment: variable ASSIGN expr
    expr: NUMBER | input | arg (PLUS|MINUS) arg
    input: VOROODI LPAREN RPAREN
    print: KHOOROOJI LPAREN list RPAREN
    list: arg (COMMA arg)*
    arg: NUMBER | variable
    variable: ID
    empty:

---

**Input**

The input consists of two parts, in the first part of the program commands are in several lines and then in a string line `-----` then the program inputs are in one line each.

 - All numbers given in the instructions must be natural and positive numbers, but the value of the variable may be negative when executing commands.

---

**Output**

  
In output, for each `print` command, its output is printed on one line. Then in one line, the number of variables that have been set at least once is output

 ---
 
**Example**

Sample input:

```undefined
A = 3
B = voroodi()
C = A + B
khoorooji(10)
khoorooji(C, 2)
-----
3
```

Sample output:

```undefined
10
6 2
3
```

---

Sample input:

```undefined
A = voroodi()
agar A == 2 : A = A + 2
khoorooji(A)
-----
2
```

Sample output:

```undefined
4
1
```

---

Sample input:

```undefined
agar 2 == 2 : A = 3
agar 3 == 4 : B = 1
khoorooji(A)
-----
```

Sample output:

```undefined
3
1
```

---
